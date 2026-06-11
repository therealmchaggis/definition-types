<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Datetime extends AbstractDataType
{
    public string $type    = 'datetime';
    public string $display = 'Datetime';
    public array $validation = [
        'valid_date' => 'Y-m-d H:i:s',
    ];
    protected array $defaults = [
        'format' => 'Y-m-d H:i:s'
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        $defaults = $this->getDefaults();
        $format = $defaults['format'];

        if (! is_null($definition)) {
            if (is_string($definition)) {
                $format = $definition;
            } else {
                if (is_string($definition['meta'])) {
                    $definition['meta'] = json_decode($definition['meta'], true);
                }
                $format = $definition['meta']['format'] ?? $format;
            }
        }

        return ($format && $value && (int) $value > 0) ? date($format, strtotime($value)) : $value;
    }

    public function convertToRaw(mixed $value, array $definition = []): mixed
    {
        if (is_string($value) && preg_match('@^([0-9]{2})/([0-9]{2})/([0-9]{4}) (.*)@', $value, $matches)) {
            $value = "{$matches[3]}-{$matches[2]}-{$matches[1]} {$matches[4]}";
        }

        return $value;
    }
}
