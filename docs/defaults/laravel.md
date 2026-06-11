# Defaults — Laravel

In Laravel, hold per-type defaults in a config file and feed them into the
registry when you bind it.

```php
// config/definition-types.php
return [
    /** @var array<string, array<string, mixed>> per-type => options */
    'defaults' => [
        'date'     => ['format' => 'd/m/Y'],
        'datetime' => ['format' => 'd/m/Y H:i'],
    ],
];
```

Apply it in the service provider that binds the registry (see
[Laravel usage](../usage/laravel.md)):

```php
$this->app->singleton(DataTypeRegistry::class, function () {
    return (new DataTypeRegistry())
        ->setDefaults(config('definition-types.defaults', []));
});
```

`setDefaults()` merges, so you can layer additional defaults at runtime on top of
the config baseline:

```php
app(DataTypeRegistry::class)->setDefaults('currency', 'symbol', '$');
```

> The planned [Laravel bridge](../../TODO.md) will ship this config file as a
> publishable asset (`php artisan vendor:publish`) and wire it automatically.

See [Defaults — overview](overview.md) for precedence and the accepted
`setDefaults()` shapes.