# CodeIgniter 4

CI4 supplies `esc()`, `is_required()`, `helper()`, `model()` and `APPPATH`, so
everything works automatically: app type overrides (`app/DataTypes`), component
overrides (`app/Views/datatype_components`) and picklist model lookups all
resolve without extra setup.

> **Heads up:** A first-class CI4 **bridge** (a shippable `Config\DataTypes`
> stub and a registered `dataTypes` service) is planned so this wiring ships
> with the package rather than living in your app. See the
> [roadmap / TODO](../../TODO.md). The setup below is the current manual
> approach and remains valid.

## The shared service

Reach the shared registry through a service. In this repo it is already wired:

```php
// app/Config/Services.php
public static function dataTypes($getShared = true): \TheRealMchaggis\DefinitionTypes\DataTypeRegistry
{
    $registry = $getShared
        ? \TheRealMchaggis\DefinitionTypes\DataTypeRegistry::instance()
        : new \TheRealMchaggis\DefinitionTypes\DataTypeRegistry();

    return $registry->setDefaults(config('DataTypes')->defaults);
}
```

```php
$registry = service('dataTypes');
echo $registry->get('date')->getFormated($value, $def);
echo $registry->renderFormComponent($definition);
```

`service('dataTypes')` returns the shared singleton and applies the defaults
configured in `Config\DataTypes`. The legacy `config('DataTypes')->getList()`
call still works and routes through the service.

## Discovery (zero registration)

Because `APPPATH` is defined, two sources are discovered automatically, with the
**app source winning**:

1. Packaged types — `…/src/Types` (`TheRealMchaggis\DefinitionTypes\Types`)
2. Application types — `app/DataTypes` (`App\DataTypes`)

The same precedence applies to form components:

1. `app/Views/datatype_components/<name>.php` ← your override
2. `…/src/Views/datatype_components/<name>.php` ← packaged default

Drop a class into `app/DataTypes/` or a view into
`app/Views/datatype_components/` and it is picked up — see
[Adding & overriding types](types.md#extending).

## Defaults

Tune per-type defaults in `Config\DataTypes`; the service applies them on every
access. See [CodeIgniter 4 defaults](../defaults/codeigniter4.md).