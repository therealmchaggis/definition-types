<?php

namespace TheRealMchaggis\DefinitionTypes;

use TheRealMchaggis\DefinitionTypes\Exceptions\ComponentNotFoundException;

/**
 * Base class for every data type.
 *
 * A data type knows three things about a value:
 *   - getFormated()      : turn a stored value into display HTML/text
 *   - getFormComponent() : render the form input used to edit the value
 *   - convertToRaw()     : turn a submitted form value back into a raw DB value
 *
 * Subtypes only implement getFormated(); the other two have sensible defaults
 * that can be overridden when a type needs special handling.
 */
abstract class AbstractDataType
{
    /**
     * The registry key for this type (e.g. 'date', 'picklist').
     * Defaults to the lower-cased class short-name when not set explicitly.
     */
    public string $type = '';

    /** Human readable label shown in pickers. */
    public string $display = '';

    /** CI4 validation rules associated with the type. */
    public array $validation = [];

    /** Packaged, per-type default options (lowest precedence). */
    protected array $defaults = [];

    /**
     * The registry that discovered this type. Injected via setRegistry() so the
     * type can read application-level defaults without depending on any global
     * singleton. Null when the type is used standalone.
     */
    protected ?DataTypeRegistry $registry = null;

    /**
     * When set, the form/render layer treats this type as another type for the
     * purpose of choosing a component (e.g. dayofweek -> picklist).
     */
    public ?string $base_type = null;

    /** Editable meta options exposed when defining a field of this type. */
    public array $meta_options = [];

    /**
     * Format a stored value for display.
     */
    abstract public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string;

    /**
     * Inject the owning registry. Called by the registry during discovery so
     * the type can resolve application-level defaults from it.
     */
    public function setRegistry(DataTypeRegistry $registry): static
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * Effective defaults for this type: packaged defaults overlaid with any
     * application defaults registered on the owning registry (registry wins).
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        $registryDefaults = $this->registry?->getDefaults($this->type) ?? [];

        return array_merge($this->defaults, $registryDefaults);
    }

    /**
     * Render the HTML form input for this type.
     *
     * The component view is resolved override-first: an app may drop a file at
     * APPPATH.'Views/datatype_components/<name>.php' to override the one shipped
     * with the package at <package>/src/Views/datatype_components/<name>.php.
     *
     * @param array $definition Field definition (id, name, display, meta, value, ...)
     * @param array $options    Extra variables made available to the component
     */
    public function getFormComponent(array $definition, array $options = []): string
    {
        $name = $this->formComponent($definition);
        $file = ComponentLocator::find($name);

        if ($file === null) {
            throw ComponentNotFoundException::forType($name);
        }

        $definition['meta'] = $this->normaliseMeta($definition['meta'] ?? []);

        $data = array_merge($definition, $options, [
            'data_type' => $this,
            'value'     => $definition['value'] ?? '',
            'meta'      => $definition['meta'],
        ]);

        return ComponentLocator::render($file, $data);
    }

    /**
     * Convert a value submitted from a form into the raw value stored in the DB.
     * Default: multi-value arrays are pipe-joined, everything else passes through.
     */
    public function convertToRaw(mixed $value, array $definition = []): mixed
    {
        if ($value === null) {
            return null;
        }

        return is_array($value) ? implode('|', $value) : $value;
    }

    /**
     * Picklist-style types return their selectable values here.
     *
     * @return array<int|string, mixed>
     */
    public function getValues(...$params): array
    {
        return [];
    }

    /**
     * The component view name to render for this type. Override to point a type
     * at a different component than its own key.
     */
    protected function formComponent(array $definition): string
    {
        return $this->base_type ?? $this->type;
    }

    /**
     * Ensure meta is an array (it is stored as JSON on definitions).
     */
    protected function normaliseMeta(mixed $meta): array
    {
        if (is_string($meta)) {
            return json_decode($meta, true) ?? [];
        }

        return is_array($meta) ? $meta : [];
    }
}
