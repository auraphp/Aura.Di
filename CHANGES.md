- DOC: In README, note that magic-call setters will not work.

- BRK: Related to testing under PHP 5.3, remove the ContainerAssertionsTrait.
  The trait is not 5.3 compatible, so it has to go. Instead, you can extend the
  Aura\Di\_Config\AbstractContainerTest in tests/container/src/ and override the
  provideGet() and provideNewInstance() methods. Sorry for the hassle.
