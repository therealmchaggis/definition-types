<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Textarea extends AbstractDataType
{
    public string $type    = 'textarea';
    public string $display = 'Multiline Textbox';

    public array $meta_options = [
        'rows' => [
            'id'          => 'rows',
            'name'        => 'rows',
            'type'        => 'number',
            'display'     => 'Rows',
            'description' => '',
            'default'     => '5',
        ],
        'max_length' => [
            'id'          => 'max_length',
            'name'        => 'max_length',
            'type'        => 'number',
            'display'     => 'No. of Characters',
            'description' => '',
        ],
        'richtext' => [
            'id'          => 'richtext',
            'name'        => 'richtext',
            'type'        => 'boolean',
            'display'     => 'Rich-Text',
            'description' => '',
        ],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        if (! is_array($definition['meta'])) {
            $definition['meta'] = json_decode($definition['meta'], true);
        }
        if (! ($definition['meta']['richtext'] ?? false)) {
            $value = nl2br($value);
        }

        return $value;
    }
}
