# Slick error handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/slickframework/error-handler/continuous-integration.yml?style=flat-square)](https://github.com/slickframework/error-handler/actions/workflows/continuous-integration.yml)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This custom PHP Error Handler is designed to intercept and manage all throwable errors, including exceptions and fatal errors
providing a robust and user-friendly error handling experience. The module captures detailed error information and presents
it within a well-structured template, offering clear and concise feedback to developers. This enhances debugging by displaying
the error type, message, file, line number, and a stack trace, all within an aesthetically pleasing and easily navigable interface.
This approach ensures a smooth user experience while simplifying error diagnosis and resolution for developers.

This package is compliant with PSR-2 code standards and PSR-4 autoload standards. It
also applies the [semantic version 2.0.0](http://semver.org) specification.

## Install

Via Composer

``` bash
$ composer require slick/error-handler
```

## Usage
In you startup script add the following:

```php
// index.php
<?php

use Slick\ErrorHandler\Runner;
use Slick\ErrorHandler\Util\SystemFacade;

require_once 'vendor/autoload.php';

$runner = new Runner(new SystemFacade());
$runner->pushHandler(fn (Throwable $throwable) => echo $throwable->getMessage())
       ->register();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email slick.framework@gmail.com instead of using the issue tracker.


## Credits

- [Slick framework](https://github.com/slickframework)
- [All Contributors](https://github.com/slickframework/error-handler/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.


[ico-version]: https://img.shields.io/packagist/v/slick/error-handler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/slickframework/error-handler.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/slick/error-handler.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/slick/error-handler
[link-scrutinizer]: https://scrutinizer-ci.com/g/slickframework/error-handler/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/slickframework/error-handler
[link-downloads]: https://packagist.org/packages/slickframework/error-handler
[link-contributors]: https://github.com/slickframework/error-handler/graphs/contributors
