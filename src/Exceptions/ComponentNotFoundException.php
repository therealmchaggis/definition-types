<?php

namespace TheRealMchaggis\DefinitionTypes\Exceptions;

use RuntimeException;

/**
 * Thrown when no form component view can be resolved for a data type.
 */
class ComponentNotFoundException extends RuntimeException
{
    public static function forType(string $type): self
    {
        return new self("No data type form component could be resolved for type: {$type}");
    }
}
