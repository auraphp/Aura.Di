# Instance Factories

Occasionally, a class will need to receive not just an instance, but a factory that is capable of creating a new instance over and over.  For example, say we have a class like the following:

```php
class ExampleNeedsFactory
{
    protected $struct_factory;

    public function __construct($struct_factory)
    {
        $this->struct_factory = $struct_factory;
    }

    public function getStruct(array $data)
    {
        $struct = $this->struct_factory->__invoke($data);
        return $struct;
    }
}

class ExampleStruct
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }
}
```

We can inject a _Factory_ that creates only _ExampleStruct_ objects using `$di->newFactory()`.

```php
$di->params['ExampleNeedsFactory']['struct_factory'] = $di->newFactory('ExampleStruct');
```

Note that the arguments passed to the factory `__invoke()` method will be passed to the underlying instance constructor sequentially, not by name. This means the `__invoke()` method works more like the native `new` keyword, and not like `$di->lazyNew()`.  These arguments override any `$di->params` values that have been set for the class being factoried; without the overrides, all existing `$di->params` values for that class will be honored. (Values from `$di->setter` for the class will also be honored, but cannot be overridden.)

Do not feel limited by the _Factory_ implementation. We can create and inject factory objects of our own if we like. The _Factory_ returned by the `$di->newFactory()` method is an occasional convenience, nothing more.

## Broader Instantiation
Sometimes you may want to more broadly leverage a configured container's ability
to instantiate objects in your classes, for example in some kind of service
locator. The `ResolutionHelper` provides a callable object which takes a
"specification" as a parameter and will retrieve a service if the container has
one appropriate to the specification or instantiate a new instance of the class
if there is no matching service name. If an array is passed as the
specification, the array is returned with the first value "resolved". This array
functionality allows for a callable to be returned referencing an method on an
instance.

```php

$helper = $di->newResolutionHelper();

$foo = $helper(\My\Foo::class);
// will return service named "My\Foo" if one is named as such
// or else a new instance of "My\Foo"

$fooBar = $helper([\My\Foo::class, 'bar']);
// will return the equivalent of [$foo, 'bar']
```

Using the `ResolutionHelper`, you can write consuming code that type hints a
callable, rather than the container itself.
