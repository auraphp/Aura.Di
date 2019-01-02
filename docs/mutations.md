# Mutate object after instantion

The _Container_ supports objects to be mutated after it is constructed.

After the _Container_ constructs a new instance of an object, you can specify which other objects will mutate the original object before locking it.

Say we have classes like the following:

```php
namespace Vendor\Package;

use Aura\Di\Injection\MutationInterface;

class Example
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}

class ExampleMutation implements MutationInterface
{
    public function mutate(object $object): object
    {
        $object->setFoo('mutated');
        return $object;
    }
}
```

We can specify that it should be mutated after construction like so:

```php
$di->mutations['Vendor\Package\Example'][] = new ExampleMutation();
```

Or lazy, like so.

```php
$di->mutations['Vendor\Package\Example'][] = $di->lazyNew(ExampleMutation::class);
```

This also allows you to create new instances of immutable objects.

> N.b.: If you try to access `$mutations` after calling `newInstance()` (or after locking the _Container_ using the `lock()` method) the _Container_ will throw an exception. This is to prevent modifying the params after objects have been created. Thus, be sure to set up all mutations for all objects before creating an object.
