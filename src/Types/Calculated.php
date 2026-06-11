<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;
use TheRealMchaggis\DefinitionTypes\Contracts\OptionalDataType;
use TheRealMchaggis\DefinitionTypes\Expression\ExpressionLanguageFactory;
use TheRealMchaggis\DefinitionTypes\Expression\ExpressionLanguageToSQL;

/**
 * Optional data type: a value computed from a Symfony ExpressionLanguage
 * expression.
 *
 * This type is only usable when the optional "symfony/expression-language"
 * package is installed (see OptionalDataType). When it is absent the registry
 * leaves `calculated` out of the available types, and referencing it by key
 * throws a MissingDependencyException.
 */
class Calculated extends AbstractDataType implements OptionalDataType
{
    public string $type    = 'calculated';
    public string $display = 'Calculated';

    public array $meta_options = [
        'expression' => [
            'id'          => 'expression',
            'name'        => 'expression',
            'type'        => 'text',
            'display'     => 'Expression',
            'description' => 'Documentation @ https://symfony.com/doc/current/reference/formats/expression_language.html',
            'validation'  => '',
        ],
    ];

    public static function isAvailable(): bool
    {
        return ExpressionLanguageFactory::isAvailable();
    }

    public static function unavailableMessage(): string
    {
        return sprintf(
            'install the optional "%s" package (composer require %s) to enable calculated fields.',
            ExpressionLanguageFactory::REQUIREMENT,
            ExpressionLanguageFactory::REQUIREMENT,
        );
    }

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return ExpressionLanguageFactory::instance()->evaluate($definition['meta']['expression'], $data);
    }

    public function prepare_definitin_for_sql(array &$definition, &$definitions = []): void
    {
        if (is_string($definition['meta'])) {
            $definition['meta'] = json_decode($definition['meta'], true);
        }
        $fields = array_column($definitions, 'name');

        $converter                     = new ExpressionLanguageToSQL();
        $definition['definition_type'] = 'calculated';
        $definition['value']           = $converter->convert($definition['meta']['expression'], $fields);

        /**
         * Adjust any virtual fields as MySQL does not allow you to reference
         * aliases. This might need rethinking, as we won't be able to reference
         * other calculated fields within calculated fields.
         */
        $virtualFields = array_filter($definitions, static fn ($field) => $field['definition_type'] === 'virtual');
        $virtualFields = array_combine(array_column($virtualFields, 'name'), array_keys($virtualFields));

        foreach ($virtualFields as $name => $guid) {
            $definition['value'] = str_replace("`{$name}`", "`{$guid}`.`value`", $definition['value']);
        }
    }
}