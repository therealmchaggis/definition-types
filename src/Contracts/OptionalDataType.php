<?php

namespace TheRealMchaggis\DefinitionTypes\Contracts;

/**
 * Marks a data type whose runtime relies on an optional dependency that the
 * package does not require (e.g. a third-party library a consumer may or may
 * not have installed).
 *
 * The registry uses this during discovery: a type that reports itself
 * unavailable is left out of getList(), so it never appears in pickers. If a
 * field definition still references it by key, the registry throws a
 * MissingDependencyException explaining how to enable the type.
 */
interface OptionalDataType
{
    /**
     * Whether this type's optional dependency is installed and the type can be
     * used. Checked without instantiating the type.
     */
    public static function isAvailable(): bool;

    /**
     * Human-readable instructions for enabling the type, surfaced in the
     * exception thrown when an unavailable type is referenced.
     */
    public static function unavailableMessage(): string;
}
