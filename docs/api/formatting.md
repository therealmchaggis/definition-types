# Display / formatting

`getFormated()` turns a stored value into display text/HTML, using the field
definition for per-field options.

```php
$type = $registry->get($def['type']);
echo $type ? $type->getFormated($value, $def) : esc($value);
```

## Signature

```php
public function getFormated(
    string $value,
    string|array|null $definition = null,
    ?array $data = null
): string;
```

- **`$value`** — the stored value to render.
- **`$definition`** — the field definition array (or its `type` string). Types
  read per-field options from `$definition['meta']`.
- **`$data`** — the full row, for types that derive their output from sibling
  fields (e.g. [`age`](../usage/calculated.md#extending-it-the-age-type)).

## Effective options

A type merges its [effective defaults](../defaults/overview.md) with the field's
`meta`, with field meta winning:

```php
$meta   = array_merge($this->getDefaults(), $this->normaliseMeta($definition['meta'] ?? []));
$format = $meta['format'];
```

## Escaping

Form output is escaped with `esc()`. CI4 provides it; everywhere else the
package's [polyfill](../usage/standalone.md#host-globals) supplies a faithful
stand-in (HTML-escapes by default, supports `raw`/`url` contexts, recurses
through arrays). The `text` type's `meta.html` option opts out of escaping for
trusted HTML.

## Next

- [DB value conversion](db-conversion.md)
- [Types](../usage/types.md)