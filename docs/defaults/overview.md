# Defaults — overview

A type can carry **default options** that fill in whatever a field's own `meta`
does not specify. The effective value is resolved with this precedence:

```
field meta   >   application defaults (registry)   >   packaged type defaults
```

- **Packaged type defaults** — declared on the type as `protected array $defaults`
  (e.g. `Date` ships with `['format' => 'Y-m-d']`).
- **Application defaults** — registered on the registry with `setDefaults()`.
- **Field meta** — the per-field `meta` array always wins.

A type reads its effective defaults with `getDefaults()`:

```php
$format = $this->getDefaults()['format'];           // packaged 'Y-m-d', or app override
$format = $definition['meta']['format'] ?? $format;  // a field may still override
```

> **No global state.** `getDefaults()` reads from the registry that discovered
> the type (the registry injects itself via `setRegistry()` during discovery),
> not from a global singleton. Two independent registries keep independent
> defaults, which keeps the package framework-agnostic and test-friendly.

## Setting defaults directly

`setDefaults()` accepts three shapes (all merge, so calls are cumulative):

```php
$registry->setDefaults('date', 'format', 'd/m/Y');          // single key/value
$registry->setDefaults('date', ['format' => 'd/m/Y']);      // one type, full config
$registry->setDefaults([                                    // bulk across types
    'date'     => ['format' => 'd/m/Y'],
    'datetime' => ['format' => 'd/m/Y H:i'],
]);
```

## Per-framework configuration

Most apps set defaults once, from config, at registry construction:

- [CodeIgniter 4 defaults](codeigniter4.md) — via `Config\DataTypes`
- [Laravel defaults](laravel.md) — via a published `config/definition-types.php`