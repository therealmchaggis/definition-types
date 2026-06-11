# Standalone PHP

The core — `DataTypeRegistry`, the type classes, `getFormated()`,
`convertToRaw()` — is **pure PHP 8.1+** with no framework dependency. A handful
of host globals are auto-detected when present and polyfilled or skipped when
not, so the same package runs unchanged standalone, under CodeIgniter 4, or
under Laravel.

## Host globals

| Global | Used by | When the host doesn't provide it |
| --- | --- | --- |
| `esc()`, `is_required()` | form components | **polyfilled** by `src/polyfill.php` (auto-loaded via Composer `files`) |
| `helper()` | component loader | skipped (`function_exists` guard) |
| `model()` | `picklist` value lookup | skipped → `getValues()` returns `[]` |
| `format_value()` | `guid` & `readonly` components | **must be supplied by the host** if those components are used |
| `APPPATH` | app override dirs | skipped (`defined()` guard); register extra dirs with `addSource()` |

## Minimal example

```php
require 'vendor/autoload.php';

use TheRealMchaggis\DefinitionTypes\DataTypeRegistry;

$registry = new DataTypeRegistry();                 // discovers the packaged types
$registry->setDefaults('date', 'format', 'd/m/Y');

echo $registry->get('date')->getFormated('2026-06-11');   // "11/06/2026"
echo $registry->renderFormComponent($definition);          // HTML — esc()/is_required() are polyfilled
```

See [Accessing the registry](../api/registry.md) for the full registry API,
[Display / formatting](../api/formatting.md) and
[DB value conversion](../api/db-conversion.md) for the value pipeline.

## Standalone caveats

- **Picklists** — no model lookup runs (`model()` is absent), so supply the
  options directly on the definition: `['type' => 'picklist', 'list' => [...]]`.
  See [Types](types.md#picklist).
- The **`guid`** and **`readonly`** components call `format_value()`; define that
  function before you render them, e.g.:

  ```php
  if (! function_exists('format_value')) {
      function format_value(mixed $value, string|array $type = 'text', array $valueList = []): string
      {
          return DataTypeRegistry::instance()->get(is_array($type) ? ($type['type'] ?? 'text') : $type)
              ?->getFormated((string) $value, is_array($type) ? $type : null) ?? (string) $value;
      }
  }
  ```

## Registering your own types

Without `APPPATH`, the app override directory isn't auto-discovered. Register
extra `(directory, namespace)` sources explicitly — later sources win:

```php
$registry->addSource(__DIR__ . '/DataTypes', 'My\\App\\DataTypes');
```

See [Adding & overriding types](types.md#extending).