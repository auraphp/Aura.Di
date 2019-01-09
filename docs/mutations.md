# Mutate object after instantion

The _Container_ supports objects to be mutated after it is constructed. This is especially useful when you have separate
container configs that both need to define the object that will be constructed. Use cases could be adding routes to a
router from multiple configs or adding commands to a console application object.

After the _Container_ constructs a new instance of an object, you can specify which other objects will mutate the 
original object before locking the container.

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
    public function __invoke(object $object): object
    {
        $object->setFoo('mutated');
        return $object;
    }
}
```

We can specify that it should be mutated after construction. We can instantiate the mutation directly or lazy.

```php
$di->mutations['Vendor\Package\Example'][] = new ExampleMutation(); // direct
$di->mutations['Vendor\Package\Example'][] = $di->lazyNew(ExampleMutation::class); // lazy
```

Just like with any other class, you inject params to the mutation class.

```php
class ExampleMutation implements MutationInterface
{
    private $argX, $argxY;
    
    public function __construct ($argX, $argY) {
        $this->argX = $argX;
        $this->argY = $argy;
    }
    
    public function __invoke(object $object): object
    {
        $object->setFoo($this->argX);
        $object->setBaz($this->argY);
        return $object;
    }
}

$di->params[ExampleMutation::class]['argX'] = $di->lazyGet('service');
$di->params[ExampleMutation::class]['argY'] = $di;
$di->mutations['Vendor\Package\Example'][] = $di->lazyNew(ExampleMutation::class);
```

When the mutation calls methods on an immutable object, you can return the new object.

```php
class RegisterRoutesMutation implements MutationInterface
{
    public function __invoke(object $object): object
    {
        $object = $object->withRoute(new Vendor\Router\Route('/contact', 'abc'));
        $object = $object->withRoute(new Vendor\Router\Route('/hello_world', 'xyz'));
        return $object;
    }
}
```
 
This also allows you to create new instances of immutable objects.

> N.b.: If you try to access `$di->mutations` after calling `newInstance()` (or after locking the _Container_ using the `lock()` method) the _Container_ will throw an exception. This is to prevent modifying the params after objects have been created. Thus, be sure to set up all mutations for all objects before creating an object.
