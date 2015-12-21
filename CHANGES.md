This is the second beta release of this library, and likely the last before a stable release (barring unexpected feature changes and bugfixes).

- (BRK) _Container_ methods `newInstance()` and `get()` now lock the _Container_ automatically. (See note below.)

- (CHG) `$di->params` now allows `null` as a parameter value.

- (ADD) ContainerConfigInterface

- (ADD) Better exception messages.

- (DOC) Add and update documentation.

* * *

Regarding auto-locking of the _Container_ after `newInstance()` and `get()`:

This prevents errors from premature unification of params/setters/values/etc. in the _Resolver_. As a result, do not use _Container_ `newInstance()` or `get()` before you are finished calling `$params`, `$setters`, `$values`, `set()`, or other methods that modify the _Container_. Use the `lazy*()` equivalents to avoid auto-locking the _Container_.
