# Contextual Injection

There are scenario's in which it is possible that you want to change the injecting object based on the object that is
being constructed. This is called contextual injection. There are two solutions to this problem. By giving two examples
it becomes clear in which case which solution is preferable to a problem.

## Example: four levels deep

An example could be a logger class that you inject into a database connection class. If the 
connection class is injected to an console application class, you want to inject a plain text logger. In case the 
connection is injected to a web application class, you may want to inject a redis logger. The connection class itself 
requires a config class, that on its turn requires the logger class.

### Solution 1: pass overwriting params to the `lazyNew` method.

```php
// define the default situation
$di->params['Vendor\Package\ConsoleApplication']['database'] = $di->lazyNew('Vendor\Package\DatabaseConnection');
$di->params['Vendor\Package\DatabaseConnection']['config'] = $di->lazyNew('Vendor\Package\Config');
$di->params['Vendor\Package\Config']['logger'] = $di->lazyNew('Vendor\Package\PlainTextLogger');
$di->params['Vendor\Package\PlainTextLogger']['param1'] = 'value1';

// define the web application context
$di->params['Vendor\Package\RedisLogger']['username'] = 'a';
$di->params['Vendor\Package\RedisLogger']['password'] = 'b';
$di->params['Vendor\Package\RedisLogger']['host'] = 'c';
$di->params['Vendor\Package\WebApplication']['config'] = $di->lazyNew('Vendor\Package\DatabaseConnection', [
    'config' => $di->lazyNew(Config::class, [
        'logger' => $di->lazyNew(RedisLogger::class)
    ])
]);
```

This solution also works with `newInstance`.

## Solution 2: contextual parameters

To this problem is also another solution. You can pass a `withContext` to the `lazyNew` method for the database 
connection injection into the web application class.

```php
// define the default situation
$di->params['Vendor\Package\ConsoleApplication']['database'] = $di->lazyNew('Vendor\Package\DatabaseConnection');
$di->params['Vendor\Package\DatabaseConnection']['config'] = $di->lazyNew('Vendor\Package\Config');
$di->params['Vendor\Package\Config']['logger'] = $di->lazyNew('Vendor\Package\PlainTextLogger');
$di->params['Vendor\Package\PlainTextLogger']['param1'] = 'value1';

// define the web application context
$di->params['Vendor\Package\RedisLogger']['username'] = 'a';
$di->params['Vendor\Package\RedisLogger']['password'] = 'b';
$di->params['Vendor\Package\RedisLogger']['host'] = 'c';
$di->params['Vendor\Package\WebApplication']['config'] = $di->lazyNew('Vendor\Package\DatabaseConnection')
    ->withContext(new Blueprint(Config::class, [
        'logger' => $di->lazyNew(RedisLogger::class)
    ]));
```

This solution does not work `newInstance`.

## Example, five levels deep

The latter solution becomes more preferable to the former when the context of the dependency tree becomes 
larger and larger, because in the latter solution one does not need to know the complete dependency tree. This is clear 
in the following scenario. Suppose the console and the web application both require the redis logger, but with 
different parameters (e.g. different host name).

Then we can have the following two solutions.

### Solution based on overwriting params

```php
// define the default situation
$di->params['Vendor\Package\ConsoleApplication']['database'] = $di->lazyNew('Vendor\Package\DatabaseConnection');
$di->params['Vendor\Package\DatabaseConnection']['config'] = $di->lazyNew('Vendor\Package\Config');
$di->params['Vendor\Package\Config']['logger'] = $di->lazyNew('Vendor\Package\RedisLogger');
$di->params['Vendor\Package\RedisLogger']['username'] = 'x';
$di->params['Vendor\Package\RedisLogger']['password'] = 'y';
$di->params['Vendor\Package\RedisLogger']['host'] = 'z';

// define the web application context
$di->params['Vendor\Package\WebApplication']['config'] = $di->lazyNew('Vendor\Package\DatabaseConnection', [
    'config' => $di->lazyNew(Config::class, [
        'logger' => $di->lazyNew(RedisLogger::class, [
            'username' => 'a'
            'password' => 'b'
            'host' => 'c'
        ])
    ])
]);
```

As becomes very clear here, the overwriting context of the web application requires to the define the complete tree to 
the change the username, password and host parameters.

### Solution based on contextual params

```php
// define the default situation
$di->params['Vendor\Package\ConsoleApplication']['database'] = $di->lazyNew('Vendor\Package\DatabaseConnection');
$di->params['Vendor\Package\DatabaseConnection']['config'] = $di->lazyNew('Vendor\Package\Config');
$di->params['Vendor\Package\Config']['logger'] = $di->lazyNew('Vendor\Package\RedisLogger');
$di->params['Vendor\Package\RedisLogger']['username'] = 'x';
$di->params['Vendor\Package\RedisLogger']['password'] = 'y';
$di->params['Vendor\Package\RedisLogger']['host'] = 'z';

// define the web application context
$di->params['Vendor\Package\WebApplication']['config'] = $di->lazyNew('Vendor\Package\DatabaseConnection')
    ->withContext(new Blueprint(RedisLogger::class, [
         'username' => 'a'
         'password' => 'b'
         'host' => 'c'
     ]));
```

Here you can see that whenever WebApplication requires a Redis logger through the database connection, it passes 
different values and there is no need to define the whole dependency tree again.