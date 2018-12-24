# CHANGELOG

This is the changelog for the 2.x series.

## 2.2.5

To remove 7.x warning, replace `list() ... each()` with `foreach`.

## 2.2.4

* Fixes #91 property-read designation causes PHPStorm to have syntax error.
  Changed @property-read to @property so they will still be auto-completed by
  IDE. Thank you David Stockton, Brandon Savage.

* Fix the doc comments.

## 2.2.3

This release provides a better message for _Exception\ReflectionFailure_, via issue #73.

## 2.2.2

This is releases moves the AbstractContainerTest to is proper location. Sorry for making two releases in a row so quickly.

## 2.2.1

This release restructures the testing and support files, particularly Composer. Note the changes in how tests are run in the new README.md.

## 2.2.0

This release has a couple of feature improvements: traits in ancestor classes and in ancestor traits are now honored, and the DI container can now be serialized and unserialized (unless it contains closures).

- ADD: The Factory now gets all traits of ancestor classes & ancestor traits.

- NEW: Class `Aura\Di\Reflection` decorates `ReflectionClass` to permit serialization of the DI Container for caching.

- FIX: The ContainerBuilder now call setAutoResolve() early, rather than late.

- FIX: If the class being factories has no `__construct()` method, instantiate without constructor.

- DOC: Update documentation and support files.

## 2.1.0

This release incorporates functionality to optionally disable auto-resolution.
By default it remains enabled, but this default may change in a future version.

- Add Container::setAutoResolve(), Factory::setAutoResolve(), etc. to allow
  disabling of auto-resolution

- When auto-resolution is disabled, Factory::newInstance() now throws
  Exception\MissingParam when a constructor param has not been defined

- ContainerBuilder::newInstance() now takes a third param to enable/disable
  auto-resolution

- AbstractContainerTest now allows you to enable/disable auto-resolve for the
  tests via a new getAutoResolve() method

## 2.0.0

- DOC: In README, note that magic-call setters will not work.

- BRK: Related to testing under PHP 5.3, remove the ContainerAssertionsTrait.
  The trait is not 5.3 compatible, so it has to go. Instead, you can extend the
  Aura\Di\_Config\AbstractContainerTest in tests/container/src/ and override the
  provideGet() and provideNewInstance() methods. Sorry for the hassle.

## 2.0.0-beta2

Second beta release.

- REF: Extract object creation from Container into Factory

- DOC: Complete README rewrite, update docblocks

- ADD: The Factory now supports setters from traits.

- ADD: LazyValue functionality.

- ADD: Auto-resolution of typehinted constructor parameters, and of array typehints with no default value, along with directed auto-resolution.

- ADD: ContainerAssertionsTrait so that outehr packages can more easily test their container config classes.

## 2.0.0-beta1

Initial 2.0 beta release.

- _Container_ v1 configurations should still work, with one exception: the `lazyCall()` method has been removed in favor of just `lazy()`. Replace `lazyCall()` with `lazy()` and all should be well.

- Now compatible with PHP 5.3.

- Uses PSR-4 autoloading instead of PSR-0.

- The package now has a series of _Lazy_ classes to represent different types of lazy behaviors, instead of using anonymous functions.

- No more cloning of _Container_ objects; that was a holdover from when we had sub-containers very early in v1 and never really used.

- Removed _Forge_ and placed functionality into _Container_.

- Removed the old _Config_ object; `$params` and `$setter` are now properties on the Container.

- No more top-level `'*'` config element.

- Renamed _Container_ `getServices()` to `getInstances()`.

- Renamed _Container_ `getDefs()` to `getServices()`.

- Added _ContainerBuilder_ and new _Config_ object for two-stage configuration.

- Now honors $setter values on interface configuration; that is, you can configure a setter on an interface, and classes implementing that interface will honor that value unless overridden by a class parent.

Thanks to HariKT, Damien Patou, Jesse Donat, jvb, and Grummfy for their contributions leading to this release!
