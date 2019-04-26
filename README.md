# Aura.Di

A serializable dependency injection container with constructor and setter injection, interface and trait awareness, configuration inheritance, and much more.

## Installation and Autoloading

This package is installable and PSR-4 autoloadable via Composer as
[aura/di][].

Alternatively, [download a release][], or clone this repository, then map the
`Aura\Di\` namespace to the package `src/` directory.

## Dependencies

This package requires PHP 7.2 or later. We recommend using the latest available version of PHP as a matter of
principle. If you are interested in using this package for older PHP versions, use version 3.x for PHP 5.5+.

Aura library packages may sometimes depend on external interfaces, but never on
external implementations. This allows compliance with community standards
without compromising flexibility. For specifics, please examine the package
[composer.json][] file.

## Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/quality-score.png?b=4.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Di/badges/coverage.png?b=4.x)](https://scrutinizer-ci.com/g/auraphp/Aura.Di/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Di.png?branch=4.x)](https://travis-ci.org/auraphp/Aura.Di)

To run the unit tests at the command line, issue `composer install` and then
`phpunit` at the package root. This requires [Composer][] to be available as
`composer`, and [PHPUnit][] to be available as `phpunit`.

This package attempts to comply with [PSR-1][], [PSR-2][], [PSR-4][] and [PSR-11][]. If
you notice compliance oversights, please send a patch via pull request.

## Community

To ask questions, provide feedback, or otherwise communicate with other Aura
users, please join our [Google Group][].

## Documentation

This package is fully documented [here](./docs/index.md).

Aura.Di 2.x and 3.x users may wish to read the [migrating](./docs/migrating.md) documentation.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-11]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md
[Composer]: http://getcomposer.org/
[PHPUnit]: http://phpunit.de/
[Google Group]: http://groups.google.com/group/auraphp
[download a release]: https://github.com/auraphp/Aura.Di/releases
[aura/di]: https://packagist.org/packages/aura/di
[composer.json]: ./composer.json
