<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Number extends AbstractDataType
{
    public string $type    = 'number';
    public string $display = 'Number';
    public array $validation = ['numeric'];

    public array $meta_options = [
        'length' => [
            'id'          => 'length',
            'name'        => 'length',
            'type'        => 'number',
            'display'     => 'Length',
            'description' => 'Note the number being stored is the number of digits in the whole number plus the number of decimal places',
            'validation'  => '',
        ],
        'dp' => [
            'id'          => 'dp',
            'name'        => 'dp',
            'type'        => 'number',
            'display'     => 'Decimal Places',
            'description' => '',
            'validation'  => '',
        ],
        'signed' => [
            'id'          => 'signed',
            'name'        => 'signed',
            'type'        => 'boolean',
            'display'     => 'Allow negative values?',
            'description' => '',
            'validation'  => '',
        ],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        if (! is_null($definition)) {
            if (is_string($definition)) {
                // No formatting metadata available.
            } else {
                if (is_string($definition['meta'])) {
                    $definition['meta'] = json_decode($definition['meta'], true);
                }

                $value = number_format((float) $value, $definition['meta']['dp'] ?? 0);

                if ($definition['meta']['lpad'] ?? false) {
                    $value = str_pad($value, $definition['meta']['lpad'], '0', STR_PAD_LEFT);
                }
                if ($definition['meta']['rpad'] ?? false) {
                    $value = str_pad($value, $definition['meta']['rpad'], '0', STR_PAD_RIGHT);
                }
            }
        }

        return $value;
    }
}
