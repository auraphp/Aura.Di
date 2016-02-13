# Getting Started

## Overview

The Aura.Di package provides a serializable dependency injection container
with the following features:

- constructor and setter injection

- inheritance of constructor parameter and setter method values from parent classes

- inheritance of setter method values from interfaces and traits

- lazy-loaded instances, services, includes/requires, and values

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

## Creating Object Instances

The most straightforward way is to create an object through the _Container_ (after locking it) is via the `newInstance()` method:

```
$object = $di->newInstance('Vendor\Package\ClassName');
```

> N.b.: The _Container_ locks itself once a new instance is produced; this ensures that the _Container_ configuration cannot be modified once objects have been created.

However, this is a relatively naive way to create objects with the _Container_. It is better to specify the various constructor parameters, setter methods, and so on, and let the _Container_ inject those values for us only when the object is used as a dependency for something else.