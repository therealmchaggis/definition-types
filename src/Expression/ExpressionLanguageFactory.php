<?php

namespace TheRealMchaggis\DefinitionTypes\Expression;

use DateTime;
use TheRealMchaggis\DefinitionTypes\Exceptions\MissingDependencyException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Builds the Symfony ExpressionLanguage instance used by the optional
 * `calculated` data type, pre-registered with the helper functions the package
 * understands (today, tomorrow, date, addDays, if).
 *
 * The factory keeps the package self-contained: it does not depend on any CI4
 * `service()`, so the calculated type works for standalone consumers too.
 * Symfony's ExpressionLanguage is an *optional* dependency, hence the
 * isAvailable() guard — every entry point that needs it throws a clear
 * MissingDependencyException when the package is absent.
 */
final class ExpressionLanguageFactory
{
    /** The optional Composer package that provides ExpressionLanguage. */
    public const REQUIREMENT = 'symfony/expression-language';

    private static ?ExpressionLanguage $shared = null;

    /**
     * Whether symfony/expression-language is installed.
     */
    public static function isAvailable(): bool
    {
        return class_exists(ExpressionLanguage::class);
    }

    /**
     * A shared, lazily-built instance.
     */
    public static function instance(): ExpressionLanguage
    {
        return self::$shared ??= self::create();
    }

    /**
     * Build a fresh, fully-configured ExpressionLanguage.
     *
     * @throws MissingDependencyException when the optional dependency is missing.
     */
    public static function create(): ExpressionLanguage
    {
        if (! self::isAvailable()) {
            throw MissingDependencyException::forType(
                'calculated',
                sprintf('install the optional "%s" package to enable expression evaluation.', self::REQUIREMENT)
            );
        }

        $language = new ExpressionLanguage();

        // Custom date helpers: each registration provides a compiler (used when
        // converting an expression to SQL) and an evaluator (used at runtime).
        $language->register(
            'if',
            static fn ($expr, $true, $false) => ($expr ? $true : $false),
            static fn (array $vars, $expr, $true, $false) => ($expr ? $true : $false),
        );
        $language->register('today', static fn () => '(new \DateTime())', static fn () => new DateTime());
        $language->register('tomorrow', static fn () => '(new \DateTime("+1 day"))', static fn () => new DateTime('+1 day'));
        $language->register(
            'addDays',
            static fn ($n, $date = '') => "(new \DateTime())->modify(\"{$date} +{$n} days\")",
            static fn (array $vars, $n, $date = '') => (new DateTime())->modify("+{$n} days"),
        );
        $language->register(
            'date',
            static fn ($date = '') => $date
                ? "(new \\DateTime(\$$date))"
                : '(new \\DateTime())',
            static fn (array $vars, $day = '') => new DateTime($day),
        );

        return $language;
    }

    /**
     * Reset the shared instance (useful in tests).
     */
    public static function reset(): void
    {
        self::$shared = null;
    }
}