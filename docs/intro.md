# Introduction

The Aura.Di package provides a dependency injection container system with the
following features:

- constructor and setter injection

- configuration of setters across interfaces and traits

- inheritance of constructor parameter and setter method values

- lazy-loaded services, values, and instances

- instance factories

- optional auto-resolution of typehinted constructor parameter values

Fully describing the nature and benefits of dependency injection, while
desirable, is beyond the scope of this document. For more information about
"inversion of control" and "dependency injection" please consult
<http://martinfowler.com/articles/injection.html> by Martin Fowler.

Finally, please note that this package is intended for use as a **dependency injection** system, not as a **service locator** system. If you use it as a service locator, that's bad, and you should feel bad.

## Container Instantiation

We instantiate a _Container_ like so:

```php
use Aura\Di\ContainerBuilder;
$builder = new ContainerBuilder();
$di = $builder->newInstance();
```

## Object Instantiation

We can then use the _Container_ to create objects for us. The most straightward
way is to use the `newInstance()` method:


```
$object = $di->newInstance('ClassName');
```

However, this is a fallback manual way to create objects. It is better to specify
constructor parameters, setter methods, and so on, and let the _Container_ create
the object automatically only when that object is actually needed.
