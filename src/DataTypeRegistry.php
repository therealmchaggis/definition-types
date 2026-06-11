<?php

namespace TheRealMchaggis\DefinitionTypes;

use TheRealMchaggis\DefinitionTypes\Contracts\OptionalDataType;
use TheRealMchaggis\DefinitionTypes\Exceptions\ComponentNotFoundException;
use TheRealMchaggis\DefinitionTypes\Exceptions\MissingDependencyException;

/**
 * Discovers and holds the available data types.
 *
 * Types are discovered from an ordered list of (directory, namespace) sources.
 * Later sources win, so an application can add new types or override packaged
 * ones simply by dropping a class into its own source directory.
 */
class DataTypeRegistry
{
    private static ?self $instance = null;
    private array $defaults = [];

    /**
     * Discovery sources as [directory => namespace], in priority order
     * (lowest priority first; later entries override earlier ones).
     *
     * @var array<string, string>
     */
    private array $sources = [];

    /**
     * Resolved types, keyed by their registry key.
     *
     * @var array<string, AbstractDataType>|null
     */
    private ?array $list = null;

    /**
     * Optional types that were discovered but whose dependency is missing,
     * keyed by registry key => the type instance (used to build a helpful
     * exception when such a type is referenced).
     *
     * @var array<string, OptionalDataType&AbstractDataType>
     */
    private array $unavailable = [];

    /**
     * @param array<string, string>|null $sources [directory => namespace]. When
     *        null, sensible CI4 defaults are used (package types + app/DataTypes).
     */
    public function __construct(?array $sources = null)
    {
        $this->sources = $sources ?? self::defaultSources();
        ComponentLocator::setPaths(self::defaultComponentPaths());
    }

    /**
     * Shared instance for framework-agnostic access (no config() required).
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Replace the shared instance (useful in tests).
     */
    public static function setInstance(?self $registry): void
    {
        self::$instance = $registry;
    }

    /**
     * Register an extra discovery source. Registered last, so it overrides
     * everything discovered so far.
     */
    public function addSource(string $directory, string $namespace): self
    {
        $this->sources[$directory] = $namespace;
        $this->list                = null;

        return $this;
    }

    /**
     * All resolved types keyed by registry key, sorted by key.
     *
     * @return array<string, AbstractDataType>
     */
    public function getList(): array
    {
        if ($this->list === null) {
            $this->list = $this->discover();
            ksort($this->list);
        }

        return $this->list;
    }

    /**
     * Fetch a single type instance by key, or null when unknown.
     *
     * @throws MissingDependencyException when the key refers to an optional
     *         type whose dependency is not installed.
     */
    public function get(string $key): ?AbstractDataType
    {
        $this->getList(); // ensure discovery has run (also populates $unavailable)

        if (isset($this->list[$key])) {
            return $this->list[$key];
        }

        if (isset($this->unavailable[$key])) {
            $type = $this->unavailable[$key];

            throw MissingDependencyException::forType($key, $type::unavailableMessage());
        }

        return null;
    }

    /**
     * Render the form component for a field definition.
     *
     * Looks the type up by $definition['type']; when a type class exists it
     * delegates to getFormComponent(), otherwise it falls back to rendering a
     * bare component view of the same name (covers component-only "types" such
     * as picklist-editor and readonly).
     */
    public function renderFormComponent(array $definition, array $options = []): string
    {
        $key  = $definition['type'] ?? 'text';
        $type = $this->get($key);

        if ($type !== null) {
            return $type->getFormComponent($definition, $options);
        }

        $file = ComponentLocator::find($key);
        if ($file === null) {
            throw ComponentNotFoundException::forType($key);
        }

        $data = array_merge($definition, $options, ['value' => $definition['value'] ?? '']);

        return ComponentLocator::render($file, $data);
    }

    /**
     * Discover and instantiate every type across all sources.
     *
     * @return array<string, AbstractDataType>
     */
    private function discover(): array
    {
        $list              = [];
        $this->unavailable = [];

        foreach ($this->sources as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (glob(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
                $class = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

                if (! class_exists($class)) {
                    continue;
                }

                $reflection = new \ReflectionClass($class);
                if ($reflection->isAbstract() || ! $reflection->isSubclassOf(AbstractDataType::class)) {
                    continue;
                }

                /** @var AbstractDataType $type */
                $type = new $class();

                // Fall back to the lower-cased short class name when no key set.
                if ($type->type === '') {
                    $type->type = strtolower($reflection->getShortName());
                }

                // Optional types whose dependency is missing are not offered as
                // usable types; they are remembered so a reference to them can
                // throw a helpful MissingDependencyException via get().
                if ($type instanceof OptionalDataType && ! $type::isAvailable()) {
                    $this->unavailable[$type->type] = $type;

                    continue;
                }

                // Inject ourselves so the type resolves defaults from this
                // registry rather than a global singleton.
                $type->setRegistry($this);

                $list[$type->type] = $type;
            }
        }

        return $list;
    }

    /**
     * @return array<string, string>
     */
    private static function defaultSources(): array
    {
        $sources = [
            // Lowest priority: types shipped with this package.
            __DIR__ . DIRECTORY_SEPARATOR . 'Types' => __NAMESPACE__ . '\\Types',
        ];

        // Highest priority: the application's own / override types.
        if (defined('APPPATH')) {
            $sources[APPPATH . 'DataTypes'] = 'App\\DataTypes';
        }

        return $sources;
    }

    /**
     * @return list<string>
     */
    private static function defaultComponentPaths(): array
    {
        $paths = [];

        if (defined('APPPATH')) {
            $paths[] = APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'datatype_components';
        }

        $paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'datatype_components';

        return $paths;
    }

    /**
     * Sets defaults
     *
     * Example usage:
     * // Single key/value
     * setDefaults('date', 'format', 'd/m/Y')
     *
     * // Full config array for one type
     * setDefaults('date', ['format' => 'd/m/Y'])
     *
     * // Bulk across multiple types
     * setDefaults([
     * 'date' => ['format' => 'd/m/Y'],
     * 'currency' => ['symbol' => '£'],
     * ])
     *
     * @param string|array $type
     * @param string|array $keyOrConfig
     * @param mixed|null $value
     * @return $this
     */
    public function setDefaults(string|array $type, string|array $keyOrConfig = [], mixed $value = null): self
    {
        if (is_array($type)) {
            // Bulk: ['date' => [...], 'currency' => [...]]
            foreach ($type as $t => $config) {
                $this->defaults[$t] = array_merge($this->defaults[$t] ?? [], $config);
            }
        } elseif (is_array($keyOrConfig)) {
            // Single type, full config array
            $this->defaults[$type] = array_merge($this->defaults[$type] ?? [], $keyOrConfig);
        } else {
            // Single type, single key/value
            $this->defaults[$type][$keyOrConfig] = $value;
        }

        return $this;
    }

    /**
     * Retunrs the defaults for a given type
     *
     * @param string $type
     * @return array
     */
    public function getDefaults(string $type): array
    {
        return $this->defaults[$type] ?? [];
    }

}
