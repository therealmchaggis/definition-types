# Laravel

The package runs under Laravel today with a small amount of manual wiring. The
core is framework-agnostic; Laravel just needs a service binding and a couple of
host conveniences.

> **Heads up:** A first-class Laravel **bridge** — an auto-discovered
> `ServiceProvider`, a publishable config file, and a picklist resolver wired to
> the container — is planned so none of the wiring below is needed. See the
> [roadmap / TODO](../../TODO.md). Until then, follow this guide.

## Register the registry

Bind the registry as a singleton in a service provider:

```php
// app/Providers/DefinitionTypesServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TheRealMchaggis\DefinitionTypes\DataTypeRegistry;

class DefinitionTypesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DataTypeRegistry::class, function () {
            $registry = (new DataTypeRegistry())
                ->setDefaults(config('definition-types.defaults', []));

            // CI4's APPPATH discovery is inactive outside CI4, so register the
            // app's own type directory explicitly if you want overrides.
            $registry->addSource(app_path('DataTypes'), 'App\\DataTypes');

            return $registry;
        });
    }
}
```

Add it to `config/app.php` (or `bootstrap/providers.php` on Laravel 11+):

```php
App\Providers\DefinitionTypesServiceProvider::class,
```

Then resolve it from the container:

```php
$registry = app(\TheRealMchaggis\DefinitionTypes\DataTypeRegistry::class);
echo $registry->get('date')->getFormated($value, $def);
echo $registry->renderFormComponent($definition);
```

## Laravel-specific notes

- **`esc()` / `is_required()`** are polyfilled by the package, so form components
  render out of the box. (Laravel ships `e()` but not `esc()` — the polyfill
  fills the gap.)
- **Picklists** — the value lookup is currently CI4-specific (`model()`), so in
  Laravel supply options via the definition `list`
  (`['type' => 'picklist', 'list' => [...]]`), or wrap your own resolver around
  an Eloquent model. A container-aware resolver is part of the planned bridge.
- **`guid` / `readonly` components** call `format_value()`; provide a matching
  helper (e.g. in `app/helpers.php`, autoloaded via Composer `files`) if you use
  those components. See the [standalone example](standalone.md#standalone-caveats).

## Defaults

Publish a `config/definition-types.php` holding your per-type defaults and pass
it into `setDefaults()` as shown above. See
[Laravel defaults](../defaults/laravel.md).