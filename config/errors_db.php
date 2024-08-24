<?php

/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Slick\ErrorHandler\Config;

return [
    "Twig\\Error\\LoaderError" => [
        'statusCode' => 500,
        "help" => <<<EOS
The error message you're encountering suggests that the template engine is unable to find the specified template
file or directory. The template engine searches for templates in the directories defined in the
`config/modules/template.php` file. If it cannot locate the template in any of these directories, it will generate
the error you're seeing.
<br />&nbsp;


**To resolve this issue:**

1. Verify that the configured directory exists.
2. Ensure the template you are trying to load is located in one of the paths specified in the
`config/modules/template.php` file.

EOS

    ],
    "Symfony\\Component\\Routing\\Exception\\ResourceNotFoundException" => [
        'statusCode' => 404,
        'help' => <<<EOS
You must define a controller with a valid route attribute to handle this request.
<br />&nbsp;
**To resolve this issue:**
1. Refer to the following example:
```php
use Symfony\Component\Routing\Attribute\Route;
class MyController
{
    #[Route(path: "%path")]
    public function doSomething(): ResponseInterface
    { // you code  }
}
```
2. Clear the router cache as follows:
```shell
$ bin/console cache:clear:routes
```

3. Create a folder of file in ```webroot%path```
EOS

    ]
];
