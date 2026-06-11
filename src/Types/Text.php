<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Text extends AbstractDataType
{
    public string $type    = 'text';
    public string $display = 'Text';

    public array $meta_options = [
        'mask' => [
            'id'          => 'mask',
            'name'        => 'mask',
            'type'        => 'text',
            'display'     => 'Input Mask',
            'description' => '',
        ],
        'length' => [
            'id'          => 'length',
            'name'        => 'length',
            'type'        => 'number',
            'display'     => 'Length',
            'description' => '',
            'validation'  => 'number',
        ],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        $escape_string = true;
        if (! is_null($definition)) {
            if (is_string($definition)) {
                $format = $definition;
            } else {
                if (is_string($definition['meta'])) {
                    $definition['meta'] = json_decode($definition['meta'], true);
                }
                $escape_string = empty($definition['meta']['html']);
            }
        }

        return $escape_string ? esc($value) : $value;
    }
}
