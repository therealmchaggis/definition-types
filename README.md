# therealmchaggis/definition-types

## Overview

Definition-driven **data types** for PHP. It is a framework-agnostic package
that can be used in CodeIgniter 4 and Laravel. It has had a long life before it
was ported to CI4 and then ported to a self-contained package.

A.I. has been used to assist in extracting the package out of the binds of CI4
and to assist with the Laravel compatability layer. I also employed A.I. to help
in the generation of the documentation.

> **Note:** This package is a work in progress. See the [Roadmap](#roadmap) for
> what's planned.

A *data type* knows how to do three things with a value that has a field *definition*:

| Responsibility | Method | Used for |
| --- | --- | --- |
| Format for display | `getFormated()` | rendering a stored value as text/HTML |
| Render an input | `getFormComponent()` | drawing the form control used to edit the value |
| Convert to raw | `convertToRaw()` | turning a submitted form value back into a DB value |

A **definition** is a plain array describing a field, e.g.:

```php
$definition = [
    'id'         => 'requested',
    'name'       => 'requested',
    'display'    => 'Requested',
    'type'       => 'date',
    'validation' => ['required','valid_date[Y-m-d]'],
    'meta'       => ['format' => 'd/m/Y'],
];
```

`type` selects the data type; `meta` carries per-field options.

---

## Installation

Requires **PHP 8.1+**. While developed in-repo it is wired as a Composer **path
repository**; once published, `composer require therealmchaggis/definition-types`
is all you need. Full instructions — including the optional `calculated`
dependency — are in [docs/installation.md](docs/installation.md).

```bash
composer require therealmchaggis/definition-types
```

---

## Compatibility at a glance

The core is **pure PHP 8.1+** with no framework dependency. A handful of host
globals are auto-detected when present and polyfilled or skipped when not, so the
same package runs unchanged across environments:

| Global | Used by | When the host doesn't provide it |
| --- | --- | --- |
| `esc()`, `is_required()` | form components | **polyfilled** by `src/polyfill.php` (auto-loaded) |
| `helper()` | component loader | skipped (`function_exists` guard) |
| `model()` | `picklist` value lookup | skipped → `getValues()` returns `[]` |
| `format_value()` | `guid` & `readonly` components | **must be supplied by the host** if those components are used |
| `APPPATH` | app override dirs | skipped (`defined()` guard); register extra dirs with `addSource()` |

| Environment | Status | Guide |
| --- | --- | --- |
| Standalone PHP | ✅ Supported | [docs/usage/standalone.md](docs/usage/standalone.md) |
| CodeIgniter 4 | ✅ Supported (manual wiring; bridge planned) | [docs/usage/codeigniter4.md](docs/usage/codeigniter4.md) |
| Laravel | ✅ Supported (manual wiring; bridge planned) | [docs/usage/laravel.md](docs/usage/laravel.md) |

---

## Documentation

- **Installation**
    - [General installation](docs/installation.md)
    - [CodeIgniter 4](docs/usage/codeigniter4.md)
    - [Laravel](docs/usage/laravel.md)

- **Usage**
    - [Standalone PHP](docs/usage/standalone.md)
    - [Types](docs/usage/types.md)
    - [Adding & overriding types](docs/usage/types.md#extending)
    - [The optional `calculated` type](docs/usage/calculated.md)

- **Registry API**
    - [Accessing the registry](docs/api/registry.md)
    - [Display / formatting](docs/api/formatting.md)
    - [DB value conversion](docs/api/db-conversion.md)

- **Defaults**
    - [Overview](docs/defaults/overview.md)
    - [CodeIgniter 4 defaults](docs/defaults/codeigniter4.md)
    - [Laravel defaults](docs/defaults/laravel.md)

---

## Roadmap

The headline next step is replacing the per-framework manual wiring with shipped
**bridges**, so each host gets a native, zero-config experience from a single
agnostic core:

- **CodeIgniter 4 bridge** — a shippable `Config\DataTypes` and registered
  `dataTypes` service.
- **Laravel bridge** — an auto-discovered `ServiceProvider`, a publishable config
  file, and a container-aware picklist resolver.
- **Picklist resolver** — decouple model lookups from CI4's `model()` so
  picklists resolve from Eloquent (Laravel) or any host.

Full plans, scope and acceptance criteria live in [TODO.md](TODO.md).