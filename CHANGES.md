- BREAK: Rename `Aura\Di\_Config\AbstractContinerTest` to `Aura\Di\AbstractContainerConfigTest`.

- BREAK: The ContainerBuilder no longer accepts pre-built services, only config class names.

- BREAK: Remove the `Aura\Di\Exception\ReflectionFailure` exception, throw the native `\ReflectionException` instead

- BREAK: Previously, the AutoResolver would supply an empty array for array typehints, and null for non-typehinted parameters. It no longer does so; it only attempts to auto-resolve class/interface typehints.

- CHANGE: Add .gitattributes file for export-ignore values

- CHANGE: Allow PHP 5.5 as the minimum version

- ADD: Allow constructor params to be specified using position number; this is in addition to specifying by $param name. Positional params take precendence over named params, to be consistent pre-existing behavior regarding merged parameters.

- DOCS: Update documentation, add bookdown files
