<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Time extends AbstractDataType
{
    public string $type    = 'time';
    public string $display = 'Time';
    public array $validation = [
        'valid_date' => 'H:i:s',
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return esc($value);
    }
}
