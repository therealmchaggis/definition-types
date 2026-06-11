# Defaults — CodeIgniter 4

`Config\DataTypes` is the tuning surface. Edit the `$defaults` array; the
`dataTypes` service applies it to the registry on every access.

```php
// app/Config/DataTypes.php
namespace Config;

class DataTypes
{
    /** @var array<string, array<string, mixed>> per-type => options */
    public array $defaults = [
        'date'     => ['format' => 'd/m/Y'],
        'datetime' => ['format' => 'd/m/Y H:i'],
    ];
}
```

The service merges those into the shared registry:

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

Because `setDefaults()` merges, you can still add more at runtime — the
`Config\DataTypes` values act as the application baseline:

```php
service('dataTypes')->setDefaults('currency', 'symbol', '$');
```

See [Defaults — overview](overview.md) for precedence and the accepted
`setDefaults()` shapes.