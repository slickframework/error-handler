<?php

/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\ErrorHandler\Util;

use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * SystemFacade
 *
 */
class SystemFacade
{
    public const ERROR_LEVELS_PHP_DEFAULTS = E_ALL;


    /**
     * Sets the error handler for the application.
     *
     * @param callable $handler The function or method to handle the errors.
     * @param int $types The types of errors to handle (self::ERROR_LEVELS_PHP_DEFAULTS by default).
     * @return callable|null The previous error handler function,
     *                        or null if no previous handler was set.
     */
    public function setErrorHandler(callable $handler, int $types = self::ERROR_LEVELS_PHP_DEFAULTS): ?callable
    {
        // Since PHP 5.4 the constant E_ALL contains all errors (even E_STRICT)
        if ($types === self::ERROR_LEVELS_PHP_DEFAULTS) {
            $types = E_ALL;
        }
        return set_error_handler($handler, $types);
    }

    /**
     * Sets the exception handler function.
     *
     * @param callable $handler The exception handler function to set.
     * @return callable|null The previous exception handler function,
     *                       or null if no previous handler was set.
     */
    public function setExceptionHandler(callable $handler): ?callable
    {
        return set_exception_handler($handler);
    }

    /**
     * @return bool
     */
    public function restoreExceptionHandler(): bool
    {
        return restore_exception_handler();
    }

    /**
     * @return bool
     */
    public function restoreErrorHandler(): bool
    {
        return restore_error_handler();
    }

    /**
     * @param callable $function
     *
     * @return void
     */
    public function registerShutdownFunction(callable $function): void
    {
        register_shutdown_function($function);
    }

    /**
     * Turns on output buffering.
     *
     * @return bool
     */
    public function startOutputBuffering(): bool
    {
        return ob_start();
    }

    /**
     * @return string|false
     */
    public function cleanOutputBuffer()
    {
        return ob_get_clean();
    }

    /**
     * @return int
     */
    public function getOutputBufferLevel()
    {
        return ob_get_level();
    }

    /**
     * @return bool
     */
    public function endOutputBuffering()
    {
        return ob_end_clean();
    }

    /**
     * @return void
     */
    public function flushOutputBuffer()
    {
        flush();
    }

    /**
     * @return int
     */
    public function getErrorReportingLevel()
    {
        return error_reporting();
    }

    /**
     * @return array{type: int, message: string, file: string, line: int}|null
     */
    public function getLastError(): ?array
    {
        return error_get_last();
    }

    /**
     * @param int $httpCode
     *
     * @return bool
     */
    public function setHttpResponseCode($httpCode): bool
    {
        if (!headers_sent()) {
            // Ensure that no 'location' header is present as otherwise this
            // will override the HTTP code being set here, and mask the
            // expected error page.
            header_remove('location');
        }

        return (bool) http_response_code($httpCode);
    }

    /**
     * @param array<string, string> $headers
     * @return bool
     */
    public function sendHeaders(array $headers): bool
    {
        $done = false;
        if (!headers_sent()) {
            foreach ($headers as $name => $value) {
                header("$name: $value");
            }
            $done = true;
        }

        return $done;
    }

    /**
     * @param int $exitStatus
     * @SuppressWarnings("PHPMD.ExitExpression")
     */
    public function stopExecution($exitStatus): void
    {
        exit($exitStatus);
    }
}
