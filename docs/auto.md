# Auto-Resolution Of Parameter Values

Auto-resolution is disabled by default. You can enable auto-resolution by instantiating the _Container_ with `$container = $builder->newInstance($builder::AUTO_RESOLVE)`. Some developers really like this feature, though we recommend against using auto-resolution because of the debugging problems inherent in tracking down the default injections.

## How Auto-Resolution Works

If there is no `$di->params` value for a parameter, the _Container_ will fill in the constructor default value.

If the parameter is typehinted as an `array` but there is no `$di->params` value and also no default value, the _Container_ will fill in an empty `[]`.

If the parameter is typehinted to a class but there is no `$di->params` value for that parameter and also no default value, the _Container_ will fill in a `lazyNew()` call to the typehinted class.

For example, look at the following class; it has a parameter with a default value, a parameter typehinted as an array with no default, and a parameter typehinted to a class with no default:

```php
class ExampleForAutoResolution
{
    public function __construct($foo = 'bar', array $baz, Example $dib)
    {
        // ...
    }
}
```

For each relevant `$di->params['ExampleForAutoResolution']` element that is missing, the _Container_ will auto-resolve the missing elements to these values:

```php
$di->params['ExampleForAutoResolution']['foo'] = 'bar';
$di->params['ExampleForAutoResolution']['baz'] = [];
$di->params['ExampleForAutoResolution']['dib'] = $di->lazyNew('Example');
```

We can set any combination of these explicitly, and those that are not explicitly set will be filled in automatically for us.

Note that we cannot auto-resolve an `array` typehint; such typehints are always resolved to an empty `[]` when no default value is present.)

Note also that auto-resolution *does not apply* to setter methods. This is because the _Container_ does not know which methods are setters and which are "normal use" methods.

## Explicitly Directing Auto-Resolution Typehints

We can direct the auto-resolution of class-typehinted constructor parameters by using the `$di->types` array.  This allows us to avoid having to specify `$di->params` for every typehinted constructor parameter in every class.  (Note that we can still specify explicit params on a specific class to override the auto-resolution.)

We can specify auto-resolution to a new instance of a class of our choosing ...

```php
// auto-resolve all 'ExampleInterface' typehints to a new 'Example' instance
$di->types['ExampleInterface'] = $di->lazyNew('Example');

// auto-resolve all 'ExampleParent' typehints to a different concrete class
$di->types['ExampleParent'] = $di->lazyNew('ExampleChild');
```

... or to a shared service (aka singleton) of our own choosing:

```php
// auto-resolve all 'Db' and 'DbInterface' typehints to a shared service
$di->types['Db'] = $di->lazyGet('db_service');
$di->types['DbInterface'] = $di->lazyGet('db_service');
```

