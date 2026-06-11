<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class File extends AbstractDataType
{
    public string $type    = 'file';
    public string $display = 'File Upload';
    public array $validation = [];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return $value;
    }
}
