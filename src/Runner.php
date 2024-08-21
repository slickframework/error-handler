<?php

/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\ErrorHandler;

use ErrorException;
use Slick\ErrorHandler\Exception\ExceptionInspector;
use Slick\ErrorHandler\Handler\HandlerInterface;
use Slick\ErrorHandler\Util\SystemFacade;
use Throwable;

/**
 * Runner
 *
 * @package Slick\ErrorHandler
 */
final class Runner implements RunnerInterface
{

    /** @var array<callable|HandlerInterface> */
    private array $handlers = [];

    public function __construct(private readonly SystemFacade $system)
    {
    }

    /**
     * @inheritDoc
     */
    public function pushHandler(callable|HandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * @inheritDoc
     */
    public function clearHandlers(): self
    {
        $this->handlers = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function register(): self
    {
        $this->system->setExceptionHandler([$this, 'handleException']);
        $this->system->setErrorHandler([$this, 'handleError']);
        $this->system->registerShutdownFunction([$this, 'handleShutdown']);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unregister(): self
    {
        $this->system->restoreExceptionHandler();
        $this->system->restoreErrorHandler();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handleException(Throwable $exception): string
    {
        $inspector = new ExceptionInspector($exception);
        $this->system->startOutputBuffering();
        // Just in case there are no handlers:
        $handlerResponse = null;

        try {
            foreach (array_reverse($this->handlers) as $handler) {
                $handlerResponse = is_callable($handler)
                    ? $handler($exception, $inspector, $this)
                    : $handler->handle($exception, $inspector, $this);

                if (in_array($handlerResponse, [HandlerInterface::LAST_HANDLER, HandlerInterface::QUIT])) {
                    break;
                }
            }
        } finally {
            $output = $this->system->cleanOutputBuffer();
        }

        if ($handlerResponse === HandlerInterface::QUIT) {
            // Cleanup all other output buffers before sending our output:
            while ($this->system->getOutputBufferLevel() > 0) {
                $this->system->endOutputBuffering();
            }

            echo $output;
            $this->system->flushOutputBuffer();
            $this->system->stopExecution(1);
        }

        return is_string($output) ? $output : '';
    }

    /**
     * @inheritDoc
     */
    public function handleError(int $level, string $message, ?string $file = null, ?int $line = null): bool
    {
        $shouldHandle = $level & $this->system->getErrorReportingLevel();
        if (!($shouldHandle)) {
            // Propagate error to the next handler, allows error_get_last() to
            // work on silenced errors.
            return false;
        }

        $exception = new ErrorException($message, 0, $level, $file, $line);
        $this->handleException($exception);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function handleShutdown(): void
    {
        $error = $this->system->getLastError();
        if ($error && $this->isLevelFatal($error['type'])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Determine if an error level is fatal (halts execution)
     *
     * @param int $level
     * @return bool
     */
    private function isLevelFatal($level): bool
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;
        return ($level & $errors) > 0;
    }

    /**
     * @inheritDoc
     * @param array<string, string> $headers
     */
    public function outputHeaders(array $headers): void
    {
        $this->system->sendHeaders($headers);
    }
}
