This release has a couple of feature improvements: traits in ancestor classes and in ancestor traits are now honored, and the DI container can now be serialized and unserialized (unless it contains closures).

- ADD: The Factory now gets all traits of ancestor classes & ancestor traits.

- NEW: Class `Aura\Di\Reflection` decorates `ReflectionClass` to permit serialization of the DI Container for caching.

- FIX: The ContainerBuilder now call setAutoResolve() early, rather than late.

- FIX: If the class being factories has no __construct() method, instantiate without constructor.

- DOC: Update documentation and support files.
