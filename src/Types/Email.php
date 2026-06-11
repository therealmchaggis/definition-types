<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Email extends AbstractDataType
{
    public string $type    = 'email';
    public string $display = 'E-mail';
    public array $validation = ['valid_email'];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return $value;
    }
}
