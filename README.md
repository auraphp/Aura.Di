# Aura.Di

The Aura.Di package provides a dependency injection container system with the
following features:

- native support for constructor- and setter-based injection

- lazy-loading of services

- inheritable configuration of setters and constructor params

- configuration of setters across interfaces and traits

When combined with factory classes, you can completely separate object
configuration, object construction, and object usage, allowing for great
flexibility and increased testability.

Fully describing the nature and benefits of dependency injection, while
desirable, is beyond the scope of this document. For more information about
"inversion of control" and "dependency injection" please consult
<http://martinfowler.com/articles/injection.html> by Martin Fowler.

## Foreword

### Installation

This library requires PHP 5.3 or later, and has no userland dependencies.

It is installable and autoloadable via Composer as [aura/di](https://packagist.org/packages/aura/di).

Alternatively, [download a release](https://github.com/auraphp/Aura.Di/releases) or clone this repository, then require or include its _autoload.php_ file.

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/quality-score.png?s=cf82e0a9aed31c10a6d493175d067b9156dcb264)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/coverage.png?s=ea28d15243b0a075c9b0e94ec93cec893e2a0ada)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Di.png?branch=develop-2)](https://travis-ci.org/auraphp/Aura.Di)

To run the [PHPUnit][] tests at the command line, go to the _tests_ directory and issue `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PHPUnit]: http://phpunit.de/manual/
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with other Aura users, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.


## Getting Started

You can instantiate a `Container` as follows:

```php
<?php
use Aura\Di\Container;
use Aura\Di\Factory;

$di = new Container(new Factory());
?>
```

### Setting Services

For the following examples, we will set a service that should return a
database connection. The hypothetical database connection class is defined as
follows:

```php
<?php
namespace Example\Package;

class Database
{
    public function __construct($hostname, $username, $password)
    {
        // ... make the database connection
    }
}
?>
```

We will proceed from naive service creation to a more sophisticated idiom in
four steps. Each of the variations is a valid use of the DI container with its
own strengths and weaknesses.

#### Variation 1: Eager Loading

In this variation, we create a service by instantiating an object with the
`new` operator.

```php
<?php
$di->set('database', new \Example\Package\Database(
    'localhost', 'user', 'passwd'
));
?>
```

This causes the database object to be created at the time we *set* the service
into the container. That means it is always created, even if we never retrieve
it from the container.

#### Variation 2: Lazy Loading

In this variation, we create a service by wrapping it in a closure, still
using the `new` operator.

```php
<?php
$di->set('database', function () {
    return new \Example\Package\Database('localhost', 'user', 'passwd');
});
?>
```

This causes the database object to be created at the time we *get* the service
from the container, using `$di->get('database')`. Wrapping the object
instantiation inside a closure allows for lazy-loading of the database object;
if we never make a call to `$di->get('database')`, the object will never be
created.

#### Variation 3: Constructor Params

In this variation, we will move away from using the `new` operator, and use
the `$di->newInstance()` method instead. We still wrap the instantiation in a
closure for lazy-loading.

```php
<?php
$di->set('database', function () use ($di) {
    return $di->newInstance('Example\Package\Database', array(
        'hostname' => 'localhost',
        'username' => 'user',
        'password' => 'passwd',
    ));
});
?>
```

The `newInstance()` method uses the `Config` object to reflect on the
constructor method of the class to be instantiated. We can then pass
constructor parameters based on their names as an array of key-value pairs.
The order of the pairs does not matter; missing parameters will use the
defaults as defined by the class constructor.

#### Variation 4: Class Constructor Params

In this variation, we define a configuration for the `Database` class
separately from the lazy-load instantiation of the `Database` object.

```php
<?php
$di->params['Example\Package\Database'] = array(
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
);

$di->set('database', function () use ($di) {
    return $di->newInstance('Example\Package\Database');
});
?>
```

As part of the object-creation process, the `Config` examines the `$di->params`
values for the class being instantiated. Those values are merged with the
class constructor defaults at instantiation time, and passed to the
constructor (again, the order does not matter, only that the param key names
match the constructor param names).

At this point, we have successfully separated object configuration from object
instantiation, and allow for lazy-loading of service objects from the
container.

#### Variation 5: Call The lazyNew() Method

In this variation, we call the `lazyNew()` method, which encapsulates the
"use a closure to return a new instance" idiom.

```php
<?php
$di->params['Example\Package\Database'] = array(
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
);

$di->set('database', $di->lazyNew('Example\Package\Database'));
?>
```

#### Variation 5a: Override Class Constructor Params

In this variation, we override the `$di->params` values that will be used at
instantiation time.

```php
<?php
$di->params['Example\Package\Database'] = array(
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
);

$di->set('database', $di->lazyNew('Example\Package\Database', array(
    'hostname' => 'example.com',
));
?>
```

The instantiation-time values take precedence over the configuration values,
which themselves take precedence over the constructor defaults.


### Getting Services

To get a service object from the container, call `$di->get()`.

```php
<?php
$db = $di->get('database');
?>
```

This will retrieve the service object from the container; if it was set using
a closure, the closure will be invoked to create the object at that time. Once
the object is created, it is retained in the container for future use; getting
the same service multiple times will return the exact same object instance.


### Constructor Params Inheritance

For the following examples, we will add an `AbstractModel` class and two
concrete classes called `BlogModel` and `WikiModel`. The idea is that all
`AbstractModel` classes need a `Database` connection to interact with one or
more tables in the database.

```php
<?php
namespace Example\Package;

abstract class AbstractModel
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}

class BlogModel extends AbstractModel
{
    // ...
}

class WikiModel extends AbstractModel
{
    // ...
}
?>
```

We will create services for the `BlogModel` and `WikiModel`, and inject the
database service into them as part of the service definition. Using config
inheritance provided by the DI container, we can define the database service
injection through class configuration.

```php
<?php
// default params for the Database class
$di->params['Example\Package\Database'] = array(
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
);

// default params for the AbstractModel class
$di->params['Example\Package\AbstractModel'] = array(
    'db' => $di->lazyGet('database'),
);

// define the database service
$di->set('database', $di->lazyNew('Example\Package\Database'));

// define the blog_model service
$di->set('blog_model', $di->lazyNew('Example\Package\BlogModel'));

// define the wiki_model service
$di->set('wiki_model', $di->lazyNew('Example\Package\WikiModel'));
?>
```

We do not need to set the value of the `'db'` param for the `BlogModel` and
`WikiModel` directly. Instead, the params for the `AbstractModel` class are
automatically inherited by the child `BlogModel` and `WikiModel` classes, so
the `'db'` constructor param for all `Model` classes automatically gets the
`'database'` service. (We can override that at instantiation time if we like.)

Note the use of the `lazyGet()` method. This is a special method intended for
use with params and setters. If we used `$di->get()`, the container would
instantiate the service at that time. However, using `$di->lazyGet()` allows
the service to be instantiated only when the object being configured is
instantiated. Think of it as a lazy-loading wrapper around the service (which
itself may be lazy-loaded).

We do not need to write our classes in any special way to get the benefit of
this configuration system. Any class with constructor params will be
recognized by the configuration system, so long as we instantiate it via
`$di->newInstance()`or `$di->lazyNew()`.


### Factories and Dependency Fulfillment

Creating a service for each of the model objects in our application can become
tiresome. We may need to create other models, and we don't want to have to
create a separate service for each one. In addition, we may need to create
model objects from within another object. Finally, we don't want to create
model objects until we actually need them. This is where we can make use of
factories.

Below, we will define three new classes: a factory to create model objects for
us, an abstract `PageController` class that uses the model factory, and a
`BlogController` class that needs an instance of a blog model. We will
populate the `ModelFactory` with a map of model names to factory objects that
will create the mapped objects.

```php
<?php
namespace Example\Package;

class ModelFactory
{
    // a map of model names to factory closures
    protected $map = array();
    
    public function __construct($map = array())
    {
        $this->map = $map;
    }
    
    public function newInstance($model_name)
    {
        $factory = $this->map[$model_name];
        $model = $factory();
        return $model;
    }
}

abstract class PageController
{
    protected $model_factory;

    public function __construct(ModelFactory $model_factory)
    {
        $this->model_factory = $model_factory;
    }
}

class BlogController extends PageController
{
    public function exec()
    {
        $blog_model = $this->model_factory->newInstance('blog');
        // ... get data from the blog model and return it ...
    }
}
?>
```

Now we can set up the DI container as follows:

```php
<?php
// default params for database connections
$di->params['Example\Package\Database'] = array(
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
);

// default params for the AbstractModel class
$di->params['Example\Package\AbstractModel'] = array(
    'db' => $di->lazyGet('database'),
);

// default params for the model factory
$di->params['Example\Package\ModelFactory'] = array(
    // a map of model names to model factories
    'map' => array(
        'blog' => $di->newFactory('Example\Package\BlogModel'),
        'wiki' => $di->newFactory('Example\Package\WikiModel'),
    ],
);

// default params for page controllers
$di->params['Example\Package\PageController'] = array(
    'model_factory' => $di->lazyGet('model_factory'),
);

// the database service; note that we can use lazyNew() and the
// container will do all the setup for us
$di->set('database', $di->lazyNew('Example\Package\Database'));

// the model factory service
$di->set('model_factory', $di->lazyNew('Example\Package\ModelFactory'));
?>
```

When we create an instance of the `BlogController` and run it ...

```php
<?php
$blog_controller = $di->newInstance('Example\Package\BlogController');
echo $blog_controller->exec();
?>
```

... a series of events occurs to fulfill all the dependencies in two steps.
The first step is the instantiation of the `BlogController`:

- The `BlogController` instance inherits its params from `PageController`

- The `PageController` params get the `'model_factory'` service

- The `ModelFactory` params get the `Database` object, creating the
  database connection at that time

The second step is the invocation of `ModelFactory::newInstance()` within
`BlogController::exec()`:

- `BlogController::exec()` invokes `ModelFactory::newInstance()`

- `ModelFactory::newInstance()` creates a new class and passes in the
  `Database` object

At the end of all this, the `BlogController::exec()` method has been able to
retrieve a fully-configured `BlogModel` object without having to specify any
configuration locally.


### Setter Injection

Until this point, we have been working via constructor injection. However, we
can work via setter injection as well.

Given the following example class ...

```php
<?php
namespace Example\Package;

class Foo {

    protected $db;

    public function setDb(Database $db)
    {
        $this->db = $db;
    }
}
?>
```

... we can define values that should be injected via setter methods:


```php
<?php
// after construction, the Container will call Foo::setDb()
// and inject the 'database' service object
$di->setter['Example\Package\Foo']['setDb'] = $di->lazyGet('database');

// create a foo_service; on get('foo_service'), the Container will create the
// Foo object, then call setDb() on it per the setter specification above.
$di->set('foo_service', $di->lazyNew('Example\Package\Foo'));
?>
```

Note that we use `lazyGet()` for the injection. As with constructor params, we
could tell the class to use a new `Database` object instead of the shared one
in the `Container`:

```php
<?php
// after construction, call Foo::setDb() and inject a service object.
// we override the default 'hostname' param for the instantiation.
$di->setter['Example\Package\Foo']['setDb'] = $di->lazyNew(
    'Example\Package\Database',
    array(
        'hostname' => 'example.com',
    )
);

// create a foo_service; on get('foo_service'), the Container will create the
// Foo object, then call setDb() on it per the setter specification above.
$di->set('foo_service', $di->lazyNew('Example\Package\Foo'));
?>
```

Setter configurations are inherited. If you have a class that extends
`Example\Package\Foo` like so ...

```php
<?php
namespace Example\Package;
class Bar extends Foo
{
    // ...
}
?>
```

... you do not need to add a new setter value for it; the `Container` reads
all parent setters and applies them. (If you do add a setter value for that
class, it will override the parent setter.)


### Conclusion

If we construct our dependencies properly with params, setters, services, and
factories, we will only need to get one object directly from DI container. All
object creation will then happen through the DI container via factory objects
and/or the `Container` object. We will never need to use the DI container 
itself in any of the created objects.
