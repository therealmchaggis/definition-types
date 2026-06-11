<?php

namespace TheRealMchaggis\DefinitionTypes\Exceptions;

use RuntimeException;

/**
 * Thrown when a field references an optional data type whose dependency is not
 * installed (see TheRealMchaggis\DefinitionTypes\Contracts\OptionalDataType).
 */
class MissingDependencyException extends RuntimeException
{
    public static function forType(string $type, string $message): self
    {
        return new self(sprintf('The "%s" data type is unavailable: %s', $type, $message));
    }
}
