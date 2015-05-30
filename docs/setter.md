# Setter Injection

The _Container_ supports setter injection in addition to constructor injection. (These can be combined as needed.)

After the _Container_ constructs a new instance of an object, we can specify that certain methods should be called with certain values immediately after instantiation by using the `$di->setter` array.  Say we have class like the following:

```php
namespace Vendor\Package;

class Example
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}
```

We can specify that, by default, the `setFoo()` method should be called with a specific value after construction like so:

```php
$di->setter['Vendor\Package\Example']['setFoo'] = 'foo_value';
```

Note also that this works only with explicitly-defined setter methods. Setter methods that exist only via magic `__call()` will not be honored.
