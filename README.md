Aura DI
=======

The Aura DI package provides a dependency injection container system with the following features:

- native support for constructor- and setter-based injection

- lazy-loading of services

- inheritable configuration of setters and constructor params

When combined with factory classes, you can completely separate object configuration, object construction, and object usage, allowing for great flexibility and increased testability.

Fully describing the nature and benefits of dependency injection, while desirable, is beyond the scope of this document. For more information about "inversion of control" and "dependency injection" please consult <http://martinfowler.com/articles/injection.html> by Martin Fowler.


Instantiating the Container
===========================

The Aura DI package comes with a instance script that returns a new DI instance:

```php
<?php
$di = require '/path/to/Aura.Di/scripts/instance.php';
```

Alternatively, you can add the Aura DI `'src/'` directory to your autoloader, and then instantiate it yourself:

```php
<?php
use Aura\Di\Container;
use Aura\Di\Forge;
use Aura\Di\Config;
$di = new Container(new Forge(new Config));
```

The `Container` is the DI container proper.  The support objects are:

- a `Config` object for collection, retrieval, and merging of setters and constructor params

- a `Forge` for object creation using the unified `Config` values

We will not need to use the support objects directly; we will get access to their behaviors through `Container` methods.


Setting Services
================

For the following examples, we will set a service that should return a database connection.  The hypothetical database connection class is defined as follows:

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
```

We will proceed from naive service creation to a more sophisticated idiom in four steps.  Each of the variations is a valid use of the DI container with its own strengths and weaknesses.

Variation 1: Eager Loading
--------------------------

In this variation, we create a service by instantiating an object with the `new` operator.

```php
<?php
$di->set('database', new \Example\Package\Database(
    'localhost', 'user', 'passwd'
));
```

This causes the database object to be created at the time we *set* the service into the container. That means it is always created, even if we never retrieve it from the container.

Variation 2: Lazy Loading
-------------------------

In this variation, we create a service by wrapping it in a closure, still using the `new` operator.

```php
<?php
$di->set('database', function() {
    return new \Example\Package\Database('localhost', 'user', 'passwd');
});
```

This causes the database object to be created at the time we *get* the service from the container, using `$di->get('database')`.  Wrapping the object instantiation inside a closure allows for lazy-loading of the database object; if we never make a call to `$di->get('database')`, the object will never be created.

Variation 3: Constructor Params
-------------------------------

In this variation, we will move away from using the `new` operator, and use the `$di->newInstance()` method instead.  We still wrap the instantiation in a closure for lazy-loading.

```php
<?php
$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database', [
        'hostname' => 'localhost',
        'username' => 'user',
        'password' => 'passwd',
    ]);
});
```

The `newInstance()` method uses the `Forge` object to reflect on the constructor method of the class to be instantiated. We can then pass constructor parameters based on their names as an array of key-value pairs.  The order of the pairs does not matter; missing parameters will use the defaults as defined by the class constructor.

Variation 4: Class Constructor Params
-------------------------------------

In this variation, we define a configuration for the `Database` class separately from the lazy-load instantiation of the `Database` object.

```php
<?php
$di->params['Example\Package\Database'] = [
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
];

$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database');
});
```

As part of the object-creation process, the `Forge` examines the `$di->params` values for the class being instantiated.  Those values are merged with the class constructor defaults at instantiation time, and passed to the constructor (again, the order does not matter, only that the param key names match the constructor param names).

At this point, we have successfully separated object configuration from object instantiation, and allow for lazy-loading of service objects from the container.

Variation 4a: Override Class Constructor Params
-----------------------------------------------

In this variation, we override the `$di->params` value at instantiation time.

```php
<?php
$di->params['Example\Package\Database'] = [
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
];

$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database', [
        'hostname' => 'example.com',
    ]);
});
```

The instantiation-time values take precedence over the configuration values, which themselves take precedence over the constructor defaults.


Getting Services
================

To get a service object from the container, call `$di->get()`.

```php
<?php
$db = $di->get('database');
```

This will retrieve the service object from the container; if it was set using a closure, the closure will be invoked to create the object at that time.  Once the object is created, it is retained in the container for future use; getting the same service multiple times will return the exact same object instance.


Constructor Params Inheritance
==============================

For the following examples, we will add an abstract `Model` class and two concrete classes called `BlogModel` and `WikiModel`. The idea is that all `Model` classes need a `Database` connection to interact with one or more tables in the database.

```php
<?php
namespace Example\Package;

abstract class Model
{
    protected $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}

class BlogModel extends Model
{
    // ...
}

class WikiModel extends Model
{
    // ...
}
```

We will create services for the `BlogModel` and `WikiModel`, and inject the database service into them as part of the service definition.

```php
<?php
// default config for the Database class
$di->params['Example\Package\Database'] = [
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
];

// a database service
$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database');
});

// a blog-model service
$di->set('blog_model', function() use ($di) {
    return $di->newInstance('Example\Package\BlogModel', [
        'db' => $di->get('database'),
    ]);
});

// a wiki-model service
$di->set('wiki_model', function() use ($di) {
    return $di->newInstance('Example\Package\WikiModel', [
        'db' => $di->get('database'),
    ]);
});
```

However, using config inheritance provided by the DI container, we can define the database service injection through class configuration.

```php
<?php
// default params for the Database class
$di->params['Example\Package\Database'] = [
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
];

// default params for the Model class
$di->params['Example\Package\Model'] = [
    'db' => $di->lazyGet('database'),
];

// define the database service
$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database');
});

// define the blog_model service
$di->set('blog_model', function() use ($di) {
    return $di->newInstance('Example\Package\BlogModel');
});

// define the wiki_model service
$di->set('wiki_model', function() use ($di) {
    return $di->newInstance('Example\Package\WikiModel');
});
```

We no longer need to set the value of `'db'` at instantiation time.  Instead, the params for the parent `Model` class are automatically inherited by the child `BlogModel` and `WikiModel` classes, so the `'db'` constructor param for all `Model` classes automatically gets the `'database'` service.  (We can override that at instantiation time if we like.)

Note the use of the `lazyGet()` method. This is a special method intended for use with params and setters.  If we used `$di->get()`, the container would instantiate the service at that time.  However, using `$di->lazyGet()` allows the service to be instantiated only when the object being configured is instantiated.  Think of it as a lazy-loading wrapper around the service (which itself may be lazy-loaded).

There is a corollary method called `lazyNew()`, which creates a new instance of a class instead of getting a service. The benefit of `lazyNew()` over `newInstance()` is the same as that of `lazyGet()` over `get()`; with `lazyNew()`, the object creation happens only when the configured class is actually created, instead of at the moment of setting.

We do not need to write our classes in any special way to get the benefit of this configuration system.  Any class with constructor params will be recognized by the configuration system, so long as we instantiate it via `$di->newInstance()`.


Factories and Dependency Fulfillment
====================================

Creating a service for each of the model objects in our application can become tiresome. We may need to create other models, and we don't want to have to create a separate service for each one.  In addition, we may need to create model objects from within another object.  This is where we can make use of factories.

Below, we will define three new classes: a factory to create model objects for us, an abstract `PageController` class that uses the model factory, and a `BlogController` class that needs an instance of a blog model.  They are defined as follows.

```php
<?php
namespace Example\Package;
use Aura\Di\Forge;

class ModelFactory
{
    protected $forge;
    
    public function __construct(Forge $forge)
    {
        $this->forge = $forge;
    }
    
    public function newInstance($model_name)
    {
        $class = 'Example\Package\Model' . ucfirst($model_name);
        return $this->forge->newInstance($class);
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
        $blog_model = $this->model_factory('blog');
        // ... get data from the blog model and return it ...
    }
}
```

Now we can set up the DI container as follows:

```php
<?php
// default params for database connections
$di->params['Example\Package\Database'] = [
    'hostname' => 'localhost',
    'username' => 'user',
    'password' => 'passwd',
];

// default params for model objects
$di->params['Example\Package\Model'] = [
    'db' => $di->lazyGet('database'),
];

// default params for the model factory
$di->params['Example\Package\ModelFactory'] = [
    'forge' => $di->getForge(),
];

// default params for page controllers
$di->params['Example\Package\PageController'] = [
    'model_factory' => $di->lazyGet('model_factory'),
];

// the database service
$di->set('database', function() use ($di) {
    return $di->newInstance('Example\Package\Database');
});

// the model factory service
$di->set('model_factory', function() use ($di) {
    return $di->newInstance('Example\Package\ModelFactory');
});
```

When we create an instance of the `BlogController` and run it ...

```php
<?php
$blog_controller = $di->newInstance('Aura\Example\BlogController');
echo $blog_controller->exec();
```

... a series of events occurs to fulfill all the dependencies in two steps. The first step is the instantation of the `BlogController`:

- The `BlogController` instance inherits its params from `PageController`

- The `PageController` params get the `'model_factory'` service

- The `ModelFactory` params get the DI container `Forge` object

The second step is the invocation of `ModelFactory::newInstance()` within `BlogController::exec()`:

- `BlogController::exec()` invokes `ModelFactory::newInstance()`

- `ModelFactory::newInstance()` calls the `Forge` object to create a new `BlogModel` (recall that the `Forge` is able to retrieve the constructor params)

- The `BlogModel` inherits its params from `Model`

- The `Model` params get the `'database'` service

At the end of all this, the `BlogController::exec()` method has been able to retrieve a fully-configured `BlogModel` object without having to specify any configuration locally.


Setter Injection
================

Until this point, we have been working via constructor injection.  However, we can work via setter injection as well.

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
```

... we can define values that should be injected via setter methods:

```php
<?php
// after construction, the Forge will call Foo::setDb()
// and inject the 'database' service object
$di->setter['Example\Package\Foo']['setDb'] = $di->lazyGet('database');

// create a foo_service; on get('foo_service'), the Forge will create the
// Foo object, then call setDb() on it per the setter specification above.
$di->set('foo_service', function() use ($di) {
    return $di->newInstance('Example\Package\Foo');
});
```

Note that we use `lazyGet()` for the injection.  As with constructor params, we could tell the class to use a new `Database` object instead of the shared one in the `Container`:

```php
<?php
// after construction, call Foo::setDb() and inject a service object.
// we override the default 'hostname' param for the instantiation.
$di->setter['Example\Package\Foo']['setDb'] = $di->lazyNew('Example\Package\Database', [
    'hostname' => 'example.com',
]);

// create a foo_service; on get('foo_service'), the Forge will create the
// Foo object, then call setDb() on it per the setter specification above.
$di->set('foo_service', function() use ($di) {
    return $di->newInstance('Example\Package\Foo');
});
```

Setter configurations are inherited.  If you have a class that extends `Example\Package\Foo` like so ...

```php
<?php
namespace Example\Package;
class Bar extends Foo
{
    // ...
}
```

... you do not need to add a new setter value for it; the `Forge` reads all parent setters and applies them.  (If you do add a setter value for that class, it will override the parent setter.)


Conclusion
==========

If we construct our dependencies properly with params, setters, services, and factories, we will only need to get one object directly from DI container.  All object creation will then happen through the DI container via factory objects and/or the `Forge` object. We will never need to use the DI container itself in any of the created objects.
