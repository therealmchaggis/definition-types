<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Url extends AbstractDataType
{
    public string $type    = 'url';
    public string $display = 'URL';
    public array $validation = ['valid_url'];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return $value;
    }
}
