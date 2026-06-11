# Roadmap & TODO

Plans for turning the framework-agnostic core into a package that gives each host
a **native, zero-config experience** — without per-framework branches or forks.

## Guiding principle

One agnostic core, **thin optional bridge layers** loaded only in their host.
The core (`DataTypeRegistry`, the type classes, `getFormated()`/`convertToRaw()`,
the expression engine) stays 100% framework-free. Everything host-specific —
service registration, config loading, model resolution, helper shims — lives in a
bridge that is activated by the framework's own discovery mechanism and is inert
everywhere else.

Target layout (all on `main`, no framework branches):

```
src/
  …                              core (framework-agnostic — done)
  Bridge/
    CodeIgniter/
      Config/DataTypes.php       publishable config stub
      Services.php               dataTypes() service factory
      DefinitionTypes.php        (optional) helper registration
    Laravel/
      DefinitionTypesServiceProvider.php
      config/definition-types.php
```

If a bridge ever grows heavy, split it into its own Composer package in a
monorepo (`therealmchaggis/definition-types-laravel`, `-ci4`) that depends on the
core — still not a branch.

---

## 0. Shared prerequisite — picklist value resolver

**Why:** `Types\Picklist::getValues()` currently calls CI4's `model()` directly
(now guarded with `function_exists('model')`). That's the last hard CI4 coupling
and the one feature that doesn't yet work in Laravel/standalone. Both bridges
need a clean seam here.

**Plan:** add an injectable resolver to `Picklist`, defaulting to the current
behaviour so CI4 is byte-for-byte unchanged.

```php
// src/Types/Picklist.php
/** @var (callable(string): ?object)|null */
private static $modelResolver = null;

public static function resolveModelsUsing(?callable $resolver): void
{
    self::$modelResolver = $resolver;
}

private function resolveModel(string $name): ?object
{
    if (self::$modelResolver !== null) {
        return (self::$modelResolver)($name);
    }
    if (function_exists('model')) {                       // CI4 default
        return model($name);
    }
    if (function_exists('app') && class_exists($name)) {  // Laravel default
        return app($name);
    }
    return null;
}
```

`getValues()` then calls `resolveModel()` instead of `model()`.

- [ ] Add `$modelResolver`, `resolveModelsUsing()`, `resolveModel()`.
- [ ] Route `getValues()` through `resolveModel()`.
- [ ] Unit tests: CI4 path (mock `model()`), injected resolver, null/standalone → `[]`.
- [ ] Document in [docs/usage/types.md](docs/usage/types.md#picklist).

**Acceptance:** CI4 picklists behave exactly as today; standalone returns `[]`
unless a resolver/`list` is provided; a host can wire any source via
`resolveModelsUsing()`.

---

## 1. `format_value()` decoupling

**Why:** the `guid` and `readonly` components call `format_value()`, an
app-level helper. Standalone/Laravel hosts must define it manually today.

**Plan:** provide a package-owned fallback (mirroring the `esc()` polyfill) that
formats via the shared registry, so the components work everywhere; hosts can
still override it.

- [ ] Add a guarded `format_value()` to `src/polyfill.php` (or a dedicated
      `Support/format_value.php`) that delegates to `DataTypeRegistry::instance()`.
- [ ] Verify `guid.php` and `readonly.php` render standalone with no host helper.
- [ ] Update [docs/usage/standalone.md](docs/usage/standalone.md) once the manual
      shim is no longer required.

---

## 2. CodeIgniter 4 bridge

**Goal:** ship the wiring that currently lives in the app (`app/Config/Services.php`,
`app/Config/DataTypes.php`) inside the package, so a CI4 app gets the service and
config for free.

- [ ] `src/Bridge/CodeIgniter/Services.php` — a `dataTypes()` factory returning the
      shared registry with `Config\DataTypes` defaults applied.
- [ ] `src/Bridge/CodeIgniter/Config/DataTypes.php` — a publishable config stub
      (per-type `$defaults`), documented for `spark` copy or manual placement.
- [ ] Confirm `APPPATH`-based discovery of `app/DataTypes` and
      `app/Views/datatype_components` still wins over packaged sources.
- [ ] Migration note: how an existing app drops its hand-rolled service in favour
      of the bridge.
- [ ] Tests against a CI4 test harness (service resolves, defaults applied,
      app overrides win, picklist `model()` lookup works).
- [ ] Refresh [docs/usage/codeigniter4.md](docs/usage/codeigniter4.md) and
      [docs/defaults/codeigniter4.md](docs/defaults/codeigniter4.md); remove the
      "manual wiring" caveats.

**Acceptance:** a fresh CI4 app requires the package, optionally publishes the
config, and uses `service('dataTypes')` with no bespoke `Services.php` entry.

---

## 3. Laravel bridge

**Goal:** auto-discovered provider + publishable config + container-wired picklist
resolver, so a Laravel app works after `composer require` with zero manual code.

- [ ] `src/Bridge/Laravel/DefinitionTypesServiceProvider.php`:
    - `register()` — bind `DataTypeRegistry` as a singleton with config defaults.
    - `boot()` — `Picklist::resolveModelsUsing(fn ($n) => app($n))` (uses the
      §0 resolver); publish config; register `app_path('DataTypes')` as a source.
- [ ] `src/Bridge/Laravel/config/definition-types.php` — publishable defaults file.
- [ ] Composer auto-discovery so no manual provider registration:

      ```jsonc
      "extra": {
          "laravel": {
              "providers": ["TheRealMchaggis\\DefinitionTypes\\Bridge\\Laravel\\DefinitionTypesServiceProvider"]
          }
      }
      ```

- [ ] Optional: a `format_value()` helper registration (or rely on §1 fallback).
- [ ] Tests with `orchestra/testbench` (provider boots, singleton resolves,
      config publishes, picklist resolves an Eloquent model, components render
      with the `esc()` polyfill).
- [ ] Refresh [docs/usage/laravel.md](docs/usage/laravel.md) and
      [docs/defaults/laravel.md](docs/defaults/laravel.md); remove the manual
      provider steps once auto-discovery lands.

**Acceptance:** `composer require therealmchaggis/definition-types` in a Laravel
app yields a working registry, picklists resolving from Eloquent, and
`php artisan vendor:publish` for config — no hand-written provider.

---

## 4. Cross-cutting

- [ ] **CI matrix** — run the test suite standalone, plus the CI4 and Laravel
      bridge suites, on PHP 8.1–8.4.
- [ ] **Naming sweep** — the package directory is still `packages/datatypes`;
      decide whether to rename to `packages/definition-types` (updates the root
      `composer.json` path repo `url`). Cosmetic; deferred.
- [ ] **`prepare_definitin_for_sql()` typo** — the method name is misspelled
      ("definitin"). Renaming is a breaking change for the app's `Age` type and
      the export pipeline; do it deliberately with a deprecation shim, not as a
      drive-by.
- [ ] **Packagist publish** — once bridges land, tag `2.0.0`, publish, and drop
      the path-repository instructions from [docs/installation.md](docs/installation.md).

---

## Done

- [x] Extracted the package from CI4 into a standalone Composer package.
- [x] Inlined the `Guid` codec (removed `App\Libraries\Guid` dependency).
- [x] Added `esc()` / `is_required()` polyfills (`src/polyfill.php`, auto-loaded).
- [x] Guarded `Picklist`'s `model()` call with `function_exists()`.
- [x] Renamed to `therealmchaggis/definition-types` /
      `TheRealMchaggis\DefinitionTypes`.
- [x] Restructured documentation into `docs/`.