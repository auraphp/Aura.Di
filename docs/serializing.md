# Serializing the Container

The _Container_ supports setter serialization. It is only possible to serialize containers that do not contain any closures.

Say we the following:

```php
$di->params[VendorClass::class] = [
    'param' => $di->lazyNew(VendorParamClass::class),
];

$di->set('fake', $di->lazyNew(VendorClass::class));
```

We can then serialize and unserialize the container:

```php
$serialized = serialize($di);
$di = unserialize($serialized);
$fakeService = $di->get('fake');
```

Serializing won't work with closures. The following example throws an exception.

```php
$di->params[VendorClass::class] = [
    'param' => $di->lazy(
        function () {
            return new VendorParamClass();
        }
    ),
];

$di->set('fake', $di->lazyNew(VendorClass::class));
serialize($di); // throws exceptions because of the closure in the params of VendorClass::class.
```