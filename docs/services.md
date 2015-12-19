# Services

A "service" is an object stored in the _Container_ under a unique name. Any time you `get()` the named service, you always get back the same object instance.

```php
// define the Example class
class Example
{
    // ...
}

// set services, then lock the container
$di->set('service_name', $di->newInstance('Example'));
$di->lock();

// get a service
$service1 = $di->get('service_name');
$service2 = $di->get('service_name');

// the two service objects are the same
var_dump($service1 === $service2); // true
```
