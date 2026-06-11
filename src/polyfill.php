<?php

/**
 * When the package runs inside CodeIgniter 4 these globals already exist and
 * the definitions below are skipped. When it runs standalone (or before CI4's
 * common functions are loaded) the fallbacks keep the data types and form
 * components working without pulling in the framework.
 *
 * This file is wired in through composer.json's "autoload.files", so it is
 * included automatically by the Composer autoloader.
 */

if (! function_exists('esc')) {
    /**
     * Minimal stand-in for CodeIgniter 4's esc() helper.
     *
     * Escapes data for safe output. Arrays are escaped recursively. Only the
     * contexts the package actually uses are implemented; unknown contexts fall
     * back to HTML escaping so output is never left unescaped by accident.
     *
     * @param mixed       $data     String or array of strings to escape.
     * @param string      $context  One of html, attr, js, css, url, raw.
     * @param string|null $encoding Character encoding (defaults to UTF-8).
     *
     * @return mixed
     */
    function esc($data, string $context = 'html', ?string $encoding = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = esc($value, $context, $encoding);
            }

            return $data;
        }

        if ($data === null || $data === '') {
            return $data;
        }

        $context  = strtolower($context);
        $encoding = $encoding ?? 'UTF-8';

        if ($context === 'raw') {
            return $data;
        }

        if ($context === 'url') {
            return rawurlencode((string) $data);
        }

        // html, attr, js and css all default to HTML-entity escaping here.
        // It is intentionally conservative: over-escaping is safe, while the
        // real CI4 escaper applies context-specific rules when present.
        return htmlspecialchars((string) $data, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
    }
}

if (! function_exists('is_required')) {
    /**
     * Minimal stand-in for the app's is_required() form helper.
     *
     * Returns true when a CI4-style validation rule string contains the
     * "required" rule, used by the form components to mark inputs required.
     *
     * @param string|null $validation Pipe-delimited validation rule string.
     */
    function is_required(?string $validation = ''): bool
    {
        if ($validation === null || $validation === '') {
            return false;
        }

        foreach (explode('|', $validation) as $rule) {
            if (strtolower(trim($rule)) === 'required') {
                return true;
            }
        }

        return false;
    }
}