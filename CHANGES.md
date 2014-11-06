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
