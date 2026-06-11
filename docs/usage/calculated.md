# The optional `calculated` type

`calculated` evaluates a [Symfony Expression Language][sel] expression — both at
display time (`getFormated()`) and, for exports, by compiling the expression to
MySQL (`prepare_definitin_for_sql()`). Because not every consumer wants that
dependency, it is **opt-in**.

[sel]: https://symfony.com/doc/current/reference/formats/expression_language.html

## Enabling it

Install the optional package:

```bash
composer require symfony/expression-language
```

That is all — the type is discovered automatically. A field then uses it like
any other type:

```php
$definition = [
    'name' => 'net',
    'type' => 'calculated',
    'meta' => ['expression' => 'gross - tax'],
];
```

## When it is not installed

The registry treats `calculated` as **unavailable** rather than broken:

- It is **left out of `getList()`**, so pickers never offer it.
- **Referencing it throws**
  `TheRealMchaggis\DefinitionTypes\Exceptions\MissingDependencyException` with a
  message telling the user to install `symfony/expression-language`:

  ```php
  $registry->get('calculated');                              // throws MissingDependencyException
  $registry->renderFormComponent(['type' => 'calculated']);  // throws the same
  ```

This is driven by the
`TheRealMchaggis\DefinitionTypes\Contracts\OptionalDataType` contract: a type
implementing it declares `isAvailable()` and `unavailableMessage()`, and the
registry gates discovery on the former and uses the latter for the exception.
Any type with a soft dependency can use the same mechanism.

## Custom expression functions

The expression instance is built by
`TheRealMchaggis\DefinitionTypes\Expression\ExpressionLanguageFactory`,
pre-registered with `today()`, `tomorrow()`, `date()`, `addDays()` and `if()`
(each with both an evaluator and a MySQL compiler). The SQL compiler lives in
`TheRealMchaggis\DefinitionTypes\Expression\ExpressionLanguageToSQL`.

## Extending it: the `age` type

The application's `App\DataTypes\Age` is an example of building on the
`calculated` type without inheriting its expression dependency. It:

- sets `public ?string $base_type = 'calculated';` so it renders through the
  calculated form component;
- emits `definition_type = 'calculated'` from its own
  `prepare_definitin_for_sql()`, so the export pipeline treats its generated SQL
  exactly like a calculated field;
- but computes its own value (a date diff) and builds its SQL from templates, so
  it needs **no** expression-language dependency and stays available regardless.

```php
namespace App\DataTypes;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Age extends AbstractDataType
{
    public string $type       = 'age';
    public string $display    = 'Age';
    public ?string $base_type = 'calculated'; // reuse the calculated component

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        // ...derive an age from one or two date fields...
    }

    public function prepare_definitin_for_sql(array &$definition, &$definitions = []): void
    {
        // ...build SQL from templates...
        $definition['definition_type'] = 'calculated'; // export as a calculated column
        $definition['value']           = $sql;
    }
}
```

Keeping `Age` in `app/DataTypes/` (rather than the package) is deliberate: its
export depends on the application's `Definitions` model. It demonstrates the
intended extension pattern — drop a class extending `AbstractDataType` into your
app's type directory and lean on an existing type's component/export behaviour.
See [Adding & overriding types](types.md#extending).