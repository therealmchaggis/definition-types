# Types

## Built-in data types

These types ship with the package and are always available. The optional
[`calculated`](calculated.md) type is documented separately.

| Key | Class | Display | Component | Notes / meta options |
| --- | --- | --- | --- | --- |
| `text` | `Types\Text` | Text | `text.php` | `mask`, `length`; `meta.html` disables escaping |
| `textarea` | `Types\Textarea` | Multiline Textbox | `textarea.php` | `rows`, `max_length`, `richtext` |
| `number` | `Types\Number` | Number | `number.php` | `length`, `dp`, `signed`; supports `lpad`/`rpad` |
| `year` | `Types\Year` | Year | `year.php` | 4-digit number input |
| `date` | `Types\Date` | Date | `date.php` | `meta.format`; `convertToRaw` d/m/Y → Y-m-d |
| `datetime` | `Types\Datetime` | Datetime | `datetime.php` | `meta.format`; `convertToRaw` d/m/Y H:i → Y-m-d H:i |
| `time` | `Types\Time` | Time | `time.php` | HTML `time` input |
| `boolean` | `Types\Boolean` | Boolean | `boolean.php` | `positive_value`, `negative_value` |
| `email` | `Types\Email` | E-mail | `text.php` | `valid_email` |
| `url` | `Types\Url` | URL | `text.php` | `valid_url` |
| `guid` | `Types\Guid` | GUID | `guid.php` | `convertToRaw` → binary; self-contained GUID codec (no app dependency) |
| `file` | `Types\File` | File Upload | `file.php` | upload widget |
| `picklist` | `Types\Picklist` | Picklist | `picklist.php` | `multiple`, `list`; `getValues()` from a model |
| `month` | `Types\Month` | Month | `picklist.php` | `base_type = picklist`; fixed month list |
| `dayofweek` | `Types\DayOfWeek` | Day of Week | `picklist.php` | `base_type = picklist`; fixed day list |

Component-only views (no type class): `picklist-editor.php`, `readonly.php`.

### picklist

The `picklist` type resolves its options in this order:

1. `definition['list']` — an explicit array of options on the field.
2. `getValues(...)` — a lookup delegated to a model with a `getValues()` method.

In CodeIgniter 4 the model lookup uses the `model()` helper automatically.
**Standalone and (currently) in Laravel**, supply options via `list`:

```php
$definition = [
    'type' => 'picklist',
    'list' => ['draft' => 'Draft', 'live' => 'Published'],
];
```

A container-aware resolver for Laravel/standalone model lookups is on the
[roadmap](../../TODO.md).

---

## Extending

Types are discovered from an ordered list of `(directory, namespace)` sources;
**later sources win**. The defaults are:

1. Packaged types — `…/src/Types` (`TheRealMchaggis\DefinitionTypes\Types`)
2. Application types — `app/DataTypes` (`App\DataTypes`) ← *overrides the package*
   (auto-discovered in CI4 via `APPPATH`; register explicitly elsewhere with
   `addSource()` — see [Standalone](standalone.md#registering-your-own-types)).

### Add a new type

Create a class extending `AbstractDataType`. Only `getFormated()` is required;
override the others when the type needs them. The example below shows the full
surface — packaged defaults, meta options, display formatting (consuming
defaults), and raw conversion:

```php
namespace App\DataTypes;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Currency extends AbstractDataType
{
    public string $type    = 'currency';   // registry key (defaults to lower-cased class name)
    public string $display = 'Currency';
    public array $validation = ['numeric'];

    /** Packaged defaults — the floor beneath app defaults and field meta. */
    protected array $defaults = [
        'symbol' => '£',
        'dp'     => 2,
    ];

    /** Options a definition may set under its `meta` key. */
    public array $meta_options = [
        'symbol' => ['id' => 'symbol', 'name' => 'symbol', 'type' => 'text',   'display' => 'Symbol'],
        'dp'     => ['id' => 'dp',     'name' => 'dp',     'type' => 'number', 'display' => 'Decimal places'],
    ];

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        $meta = array_merge($this->getDefaults(), $this->normaliseMeta($definition['meta'] ?? []));

        return $meta['symbol'] . number_format((float) $value, (int) $meta['dp']);
    }

    /** Strip grouping/symbol so a clean decimal reaches the database. */
    public function convertToRaw(mixed $value, array $definition = []): mixed
    {
        return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
    }
}
```

It is picked up automatically — no registration needed. With the type in place,
an app default flows through as expected:

```php
$registry->setDefaults('currency', 'symbol', '$');           // or via config
$registry->get('currency')->getFormated('1234.5');           // "$1,234.50"
$registry->get('currency')->getFormated('1234.5', [
    'meta' => ['symbol' => '€', 'dp' => 0],                  // field meta wins
]);                                                          // "€1,235"
```

To give the type its own form input, add a matching component (see
[Overriding form components](#overriding-form-components)); otherwise it reuses
whatever component its `formComponent()` resolves to.

### Override a built-in type

Declare a class with the **same `$type` key** (e.g. `$type = 'date'`) in a source
scanned after the package (the app source). It replaces the packaged `Date`.

---

## Overriding form components

Components are resolved override-first across these directories:

1. `app/Views/datatype_components/<name>.php` ← *your override*
2. `…/src/Views/datatype_components/<name>.php` ← *packaged default*

To customise the date input, create `app/Views/datatype_components/date.php`. The
component is rendered with the field definition extracted into scope (`$id`,
`$name`, `$display`, `$validation`, `$meta`, `$value`, …) plus `$data_type` (the
type instance). `esc()` and `is_required()` are always available — CI4 provides
them, and the package [polyfills](standalone.md#host-globals) them everywhere
else.

A type chooses its component via `protected function formComponent()`, which
defaults to `base_type ?? type`. Override it to point at a different component.

---

## SQL export: `prepare_definitin_for_sql()`

Some types (the optional [`calculated`](calculated.md) type, and the app's `age`
type) implement an optional `prepare_definitin_for_sql()` method used when
building export SQL. It is called opportunistically via `method_exists()`, so it
is **not** part of the base contract — implement it only when a type needs to
emit SQL.