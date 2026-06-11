<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Boolean extends AbstractDataType
{
    public string $type    = 'boolean';
    public string $display = 'Boolean';
    public array $validation = [
        'in_list' => [0, 1, 'yes', 'no', 'on', 'off'],
    ];

    public array $positive_values = [1, true, 'yes', 'on'];
    public array $negative_values = [null, 0, false, 'no', 'off'];

    public array $meta_options = [
        'positive_value' => [
            'id'          => 'positive_value',
            'name'        => 'positive_value',
            'type'        => 'text',
            'display'     => 'Positive Display Value',
            'description' => 'This is the value to print when this data type is set to a positive value.',
            'validation'  => 'text',
        ],
        'negative_value' => [
            'id'          => 'negative_value',
            'name'        => 'negative_value',
            'type'        => 'text',
            'display'     => 'Negative Display Value',
            'description' => 'This is the value to print when this data type is set to a negative value.',
            'validation'  => 'text',
        ],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        if (! is_null($definition)) {
            if (is_string($definition['meta'])) {
                $definition['meta'] = json_decode($definition['meta'], true);
            }
            $positive = $definition['meta']['positive_value'] ?? 'on';
            $negative = $definition['meta']['negative_value'] ?? 'off';

            return in_array($value, $this->positive_values) ? $positive : $negative;
        }

        return $value;
    }
}
