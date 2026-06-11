# Installation

`therealmchaggis/definition-types` requires **PHP 8.1+** and has no mandatory
framework dependency. The only optional dependency is
`symfony/expression-language`, needed solely by the [`calculated`
type](usage/calculated.md).

## From Packagist (once published)

```bash
composer require therealmchaggis/definition-types
```

## As an in-repo path repository (current development setup)

While the package lives inside this monorepo it is wired as a Composer **path
repository**. In the application's root `composer.json`:

```jsonc
"require": {
    "therealmchaggis/definition-types": "*"
},
"repositories": [
    { "type": "path", "url": "packages/datatypes", "options": { "symlink": true } }
]
```

```bash
composer update therealmchaggis/definition-types
```

> The path repository's `url` still points at `packages/datatypes` — the
> directory name was intentionally left unchanged during the rename. When the
> package is published to Packagist, drop the `repositories` block entirely.

## Optional: the `calculated` type

The expression-based [`calculated` type](usage/calculated.md) needs Symfony's
Expression Language. It is opt-in — install it only if you use that type:

```bash
composer require symfony/expression-language
```

## Framework integration

The package runs anywhere PHP does. For host-specific wiring, see:

- [Standalone PHP](usage/standalone.md)
- [CodeIgniter 4](usage/codeigniter4.md)
- [Laravel](usage/laravel.md)