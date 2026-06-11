<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Year extends AbstractDataType
{
    public string $type    = 'year';
    public string $display = 'Year';
    public array $validation = ['numeric'];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return $value;
    }
}
