# DB value conversion

`convertToRaw()` turns a submitted form value back into the value you store in
the database — the inverse of display formatting.

```php
$type = $registry->get($def['type'] ?? 'text');
$raw  = $type ? $type->convertToRaw($value, $def) : $value;
```

## Signature

```php
public function convertToRaw(mixed $value, array $definition = []): mixed;
```

Most types pass the value through unchanged. Types override it when the stored
representation differs from the input representation, for example:

| Type | Input | Stored (raw) |
| --- | --- | --- |
| `date` | `31/12/2026` (d/m/Y) | `2026-12-31` (Y-m-d) |
| `datetime` | `31/12/2026 09:30` | `2026-12-31 09:30` |
| `guid` | `5b6f…` string GUID | 16-byte binary (`|`-joined for arrays) |
| `number` | `1,234.50` | `1234.50` |

## Null handling

Conversions are null-safe — a `null` value returns `null` rather than being
coerced. Always resolve the type first and fall back to the raw value when the
type is unknown, as in the snippet above.

## Next

- [Display / formatting](formatting.md)
- [Types](../usage/types.md)