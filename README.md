# Aura.Di

The Aura.Di package provides a dependency injection container system with the
following features:

- constructor and setter injection

- explicit and implicit auto-resolution of typehinted constructor parameter values

- configuration of setters across interfaces and traits

- inheritance of constructor parameter and setter method values

- lazy-loaded services, values, and instances

- instance factories

Fully describing the nature and benefits of dependency injection, while
desirable, is beyond the scope of this document. For more information about
"inversion of control" and "dependency injection" please consult
<http://martinfowler.com/articles/injection.html> by Martin Fowler.

Finally, please note that this package is intended for use as a **dependency injection** system, not as a **service locator** system. If you use it as a service locator, that's bad, and you should feel bad.

**WARNING:** This 3.x package is still under heavy development. Use at your own risk. You may wish to review the 2.x package for a stable version.

## Installation

This package is installable and autoloadable via Composer as [aura/di](https://packagist.org/packages/aura/di).

Alternatively, [download a release](https://github.com/auraphp/Aura.Di/releases) or clone this repository, then require or include its _autoload.php_ file.

## Dependencies

This package requires PHP 5.6 or later. We recommend using the latest available version of PHP as a matter of principle.

The source code depends on the `"container-interop/container-interop": "~1.0"` interface package.

The test code depends on `"mouf/picotainer": "~1.0"` and `"acclimate/container": "~1.0"` for integration testing only.

## Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/quality-score.png?b=3.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/coverage.png?b=3.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Di.png?branch=3.x)](https://travis-ci.org/auraphp/Aura.Di)

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This package attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Community

To ask questions, provide feedback, or otherwise communicate with other Aura users, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.

## Documentation

This package is fully documented [here](./tree/3.x/docs/index.md).
