# Class, Interface, and Trait Inheritance

Whether by constructor parameters or setter methods, each class instantiated through the _Container_ "inherits" the values of its parents by default. This means we can set a constructor parameter or setter method value on a parent class, and the child class will use it (that is, unless we set an overriding value on the child class).

## Constructor Parameter Inheritance

Let's say we have this parent class and this child class:

```php
class ExampleParent
{
    protected $foo;
    protected $bar;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}

class ExampleChild extends ExampleParent
{
    protected $baz;

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }
}
```

We can specify the default values for every class that extends _ExampleParent_ through the `$di->params` and `$di->setter` values for the _ExampleParent_.

```php
$di->params['ExampleParent']['foo'] = 'parent_foo';
$di->setter['ExampleParent']['setBar'] = 'parent_bar';
```

When we call `$di->newInstance('ExampleChild')`, the child class will have inherited the defaults from the parent.

We can always override the inherited values by specifying them for the child class directly:

```php
$di->params['ExampleChild']['foo'] = 'child_foo';
$di->setter['ExampleChild']['setBaz'] = 'child_baz';
```

Note that classes extended from the child class will then inherit those new values. In this way, constructor parameter and setter method values are propagated down the inheritance hierarchy.



### Setter Method Inheritance

If a class uses a setter method, whether by extending a parent class, using a trait, or implementing an interface, we can specify the default value for that setter method in relation to the parent class, trait, or interface. That value will then be applied by default in every class that extends that parent class, uses that trait, or implements that interface.

For example, let's say we have this trait, interface, and class:

```php
trait ExampleFooTrait
{
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}

interface ExampleBarInterface
{
    public function setBar($bar);
}

class ExampleWithTraitAndInterface implements ExampleBarInterface
{
    use ExampleFooTrait;

    protected $foo;
    protected $bar;

    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}
```

We then define the default setter method values on the trait and interface:

```php
$di->setter['ExampleFooTrait']['setFoo'] = 'foo_value';
$di->setter['ExampleBarInterface']['setBar'] = 'bar_value';
```

When we call `$di->newInstance('ExampleWithTraitAndInterface')`, those setter methods will be called by the _Container_ with those values.

Note that if we have class-specific `$di->setter` values, those will take precedence over the trait and interface setter values.
