<?php

namespace TheRealMchaggis\DefinitionTypes;

/**
 * Resolves a form-component view file by name across an ordered list of
 * directories. Earlier directories win, so an application can override a
 * packaged component simply by placing a same-named file in its own directory.
 */
class ComponentLocator
{
    /**
     * Ordered list of directories searched for "<name>.php". The first match
     * wins. Set by DataTypeRegistry at boot; falls back to the package's own
     * components (plus the app override dir when running inside CI4).
     *
     * @var list<string>|null
     */
    private static ?array $paths = null;

    /**
     * @param list<string> $paths Ordered, override-first.
     */
    public static function setPaths(array $paths): void
    {
        self::$paths = array_values(array_filter($paths));
    }

    /**
     * @return list<string>
     */
    public static function paths(): array
    {
        if (self::$paths === null) {
            self::$paths = self::defaultPaths();
        }

        return self::$paths;
    }

    /**
     * Return the absolute path of the first matching component file, or null.
     */
    public static function find(string $name): ?string
    {
        foreach (self::paths() as $dir) {
            $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name . '.php';
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Render a component file in an isolated scope.
     *
     * Native include + output buffering is used (rather than CI4's view())
     * because the file is resolved by absolute path with an app-over-package
     * fallback, which view() cannot express cleanly. CI4 helpers used by the
     * components (esc(), is_required()) are loaded first when available.
     */
    public static function render(string $file, array $data): string
    {
        if (function_exists('helper')) {
            helper(['form', 'definitions']);
        }

        $render = static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            include $__file;

            return (string) ob_get_clean();
        };

        return $render($file, $data);
    }

    /**
     * @return list<string>
     */
    private static function defaultPaths(): array
    {
        $paths = [];

        // App override directory takes precedence when running inside CI4.
        if (defined('APPPATH')) {
            $paths[] = APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'datatype_components';
        }

        // The components shipped with this package.
        $paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'datatype_components';

        return $paths;
    }
}
