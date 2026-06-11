<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Picklist extends AbstractDataType
{
    public string $type    = 'picklist';
    public string $display = 'Picklist';


    /** @var (callable(string): ?object)|null */
    private static $modelResolver = null;


    public array $meta_options = [
        'multiple' => [
            'id'          => 'multiple',
            'name'        => 'multiple',
            'type'        => 'boolean',
            'display'     => 'Multiple values?',
            'description' => '',
            'validation'  => '',
        ],
        'list' => [
            'id'          => 'list',
            'name'        => 'list',
            'type'        => 'picklist-editor',
            'display'     => 'Values',
            'description' => '',
            'validation'  => '',
        ],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        $values = $definition['list'] ?? $this->getValues();
        if (isset($values[$value])) {
            return $values[$value];
        }
        if (isset($values[0]->value)) {
            for ($x = 0; $x < count($values); $x++) {
                if ($values[$x]->value === $value) {
                    return $values[$x]->display;
                }
            }

            return $value;
        }

        return $value;
    }

    /**
     * Override how picklist value-source models are resolved.
     * Hosts (Laravel, etc.) call this once during bootstrap.
     */
    public static function resolveModelsUsing(?callable $resolver): void
    {
        self::$modelResolver = $resolver;
    }

    /**
     * Resolve the model for the given name.
     *
     * @param string $name
     * @return object|string|null
     */
    private function resolveModel(string $name): ?object
    {
        if (self::$modelResolver !== null) {
            return (self::$modelResolver)($name);
        }
        if (function_exists('model')) {                       // CI4
            return model($name);
        }
        if (function_exists('app') && class_exists($name)) {  // Laravel default
            return app($name);
        }
        return null;
    }

    public function getValues(...$params): array
    {
        if (empty($params)) {
            return [];
        }

        $model = $this->resolveModel($params[0]);
        if ($model !== null && method_exists($model, 'getValues')) {
            array_shift($params);

            return call_user_func_array([$model, 'getValues'], $params);
        }

        return [];
    }
}
