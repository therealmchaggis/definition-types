# Accessing the registry

`DataTypeRegistry` discovers and holds every type. Reach it however suits your
host — the core is **not** tied to `config()`:

```php
use TheRealMchaggis\DefinitionTypes\DataTypeRegistry;

$registry = service('dataTypes');         // CI4 shared service (recommended in CI4)
$registry = app(DataTypeRegistry::class); // Laravel container singleton
$registry = DataTypeRegistry::instance(); // framework-agnostic shared singleton
$registry = new DataTypeRegistry();       // standalone (pass custom sources if you like)
```

`DataTypeRegistry::instance()` returns a process-wide shared singleton with no
framework requirement; `new DataTypeRegistry()` gives an independent instance
with its own discovered types and defaults (useful in tests).

## API

```php
$registry->getList(): array;                                   // ['date' => Date, ...] sorted by key
$registry->get(string $key): ?AbstractDataType;                // one type, or null
$registry->renderFormComponent(array $def, array $opts = []);  // HTML for a field's input
$registry->addSource(string $dir, string $namespace): self;    // register an extra type dir
$registry->setDefaults(...): self;                             // application defaults (see Defaults)
$registry->getDefaults(string $type): array;                   // defaults registered for one type
```

- **`getList()`** returns every available type keyed by its `$type`, sorted by
  key. Unavailable optional types (e.g. [`calculated`](../usage/calculated.md)
  without `symfony/expression-language`) are omitted.
- **`get()`** returns a single type instance, or `null` if unknown. Referencing a
  registered-but-unavailable optional type throws
  `TheRealMchaggis\DefinitionTypes\Exceptions\MissingDependencyException`.
- **`renderFormComponent()`** looks the type up by `$def['type']`, then renders
  its component. When no component can be resolved it throws
  `TheRealMchaggis\DefinitionTypes\Exceptions\ComponentNotFoundException` so
  callers can fall back gracefully.
- **`addSource()`** registers an extra `(directory, namespace)` pair for type
  discovery; later sources win. See
  [Adding & overriding types](../usage/types.md#extending).
- **`setDefaults()` / `getDefaults()`** — see [Defaults](../defaults/overview.md).

## Next

- [Display / formatting](formatting.md)
- [DB value conversion](db-conversion.md)