<?php

/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\ErrorHandler\Handler;

use Slick\ErrorHandler\Exception\ExceptionInspector;
use Slick\ErrorHandler\RunnerInterface;
use Throwable;

/**
 * HandlerInterface
 *
 * @package Slick\ErrorHandler\Handler
 */
interface HandlerInterface
{
    /*
     Return constants that can be returned from Handler::handle
     to message the handler walker.
     */
    const DONE         = 0x10; // returning this is optional, only exists for
    // semantic purposes
    /**
     * The Handler has handled the Throwable in some way, and wishes to skip any other Handler.
     * Execution will continue.
     */
    const LAST_HANDLER = 0x20;
    /**
     * The Handler has handled the Throwable in some way, and wishes to quit/stop execution
     */
    const QUIT         = 0x30;

    public function handle(Throwable $throwable, ExceptionInspector $inspector, RunnerInterface $runner): ?int;
}
