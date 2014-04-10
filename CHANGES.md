Initial 2.0 beta release.

- _Container_ v1 configurations should still work, with one exception: the `lazyCall()` method has been removed in favor of just `lazy()`. Replace `lazyCall()` with `lazy()` and all should be well.

- Now compatible with PHP 5.3.

- Uses PSR-4 autoloading instead of PSR-0.

- The package now has a series of _Lazy_ classes to represent different types of lazy behaviors, instead of using anonymous functions.

- No more cloning of _Container_ objects; that was a holdover from when we had sub-containers very early in v1 and never really used.

- Removed _Forge_ and placed functionality into _Container_.

- Removed the old _Config_ object; `$params` and `$setter` are now properties on the Container.

- No more top-level '*' config element.

- Renamed _Container_ `getServices()` to `getInstances()`.

- Renamed _Container_ `getDefs()` to `getServices()`.

- Added _ContainerBuilder_ and new _Config_ object for two-stage configuration.

- Now honors $setter values on interface configuration; that is, you can configure a setter on an interface, and classes implementing that interface will honor that value unless overridden by a class parent.

Thanks to HariKT, Damien Patou, Jesse Donat, jvb, and Grummfy for their contributions leading to this release!
