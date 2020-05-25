# Migrating

This document helps to upgrade users moving from 3.x to 4.x. Below you can also (still) find the upgrade guide for
people upgrading from 2.x to 3.x.

## Migrating from 3.x to 4.x

Aura.Di 4.x is largely similar to 3.x, but there are some backwards-compatibility breaks, as well as some new features.

### Dropped PHP 7.1 and lower, no HHVM

Make sure you use the correct PHP version. This library uses the object type-hint and therefore requires PHP 7.2+. The
support for HHVM is dropped.

### Type-hinting

All classes are now using type hinting for method arguments and return types, including the `void` return type. This 
means if you have extended or implemented one of the library classes in your code, you will have to update the 
signatures of your extended/implemented methods to match the new signatures.

For most users this involves only updating classes implementing the `ContainerConfigInterface`.

### InjectionFactory::newInstance()

`Aura\Di\Injection\InjectionFactory::newInstance()` now requires an instance of `Aura\Di\Resolver\Blueprint` to be passed as the parameter, instead of the former classname, parameter array and setter array.

### Container Interop

From version 4.x the library will implement the official container standard interface (PSR 11) instead of the
container-interop interface.

### Dropped features

LazyRequire and LazyInclude could be constructed with a lazy object instead of a filename. This was probably only used
for testing purposes (it was not included in any docs) but is included here in case someone is actually using this. 


## Migrating from 2.x to 3.x

Aura.Di 3.x is largely similar to 2.x, but there are some backwards-compatibility breaks, as well as some new features.

### BC Breaks

#### Instantiation

The way the container is instantiated has been changed from this ...

```php
use Aura\Di\Container;
use Aura\Di\Factory;
use Aura\Di\ContainerBuilder;

$di = new Container(new Factory);

// or

$container_builder = new ContainerBuilder();
$di = $container_builder->newInstance(
    array(),
    array(),
    $auto_resolve = false
);
```

... to this:

```php
use Aura\Di\ContainerBuilder;

$container_builder = new ContainerBuilder();

// use the builder to create and configure a container
// using an array of ContainerConfig classes
$di = $container_builder->newConfiguredInstance([
    'Aura\Cli\_Config\Common',
    'Aura\Router\_Config\Common',
    'Aura\Web\_Config\Common',
]);
```

#### `setter` vs `setters`

Use of `$di->setter` in 2.x is now `$di->setters` in 3.x. Please note there is an additional [`s` in the end](https://github.com/auraphp/Aura.Di/issues/115).

#### Automatic Locking

The container now calls `lock()` automatically when you call `get()` or `newInstance()`, so make sure everything is lazy-loaded, or else you will run into something like [cannot modify container when locked](https://github.com/auraphp/Aura.Di/issues/118).

#### Config vs ContainerConfig

[`Aura\Di\Config`](https://github.com/auraphp/Aura.Di/blob/2.2.4/src/Config.php) in 2.x is now [`Aura\Di\ContainerConfig`](https://github.com/auraphp/Aura.Di/blob/3.0.0/src/ContainerConfig.php) in 3.x.

### Features

#### lazyGetCall()

Example taken from [Radar](https://github.com/radarphp/Radar.Adr/blob/0b4fa74c4939a715562d60e37c1976fc59b420b6/src/Config.php#L50):

```php
$di->params['Radar\Adr\Handler\RoutingHandler']['matcher'] = $di->lazyGetCall('radar/adr:router', 'getMatcher');
```

Here the value assigned to `matcher` is taken from the [RouterContainer](https://github.com/auraphp/Aura.Router/blob/3.0.0/src/RouterContainer.php#L263-L273) `getMatcher()` method.

#### Instance Factories

An instance factory creates multiple instances of the same class; [refer the docs](http://auraphp.com/packages/3.x/Di/factories.html) for more information.
