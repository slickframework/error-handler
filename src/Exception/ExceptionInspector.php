<?php

/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\ErrorHandler\Exception;

use Throwable;

/**
 * ExceptionInspector
 *
 * @package Slick\ErrorHandler\Exception
 */
class ExceptionInspector
{

    public function __construct(private Throwable $throwable, private string $applicationRoot = '')
    {
    }

    /**
     * Returns the name of the exception.
     *
     * @return string The name of the exception.
     */
    public function exceptionName(): string
    {
        return $this->parseName($this->throwable);
    }

    /**
     * Get the path of exceptions thrown in reverse order.
     *
     * @return array<string> The reversed array of exception paths.
     */
    public function exceptionsPath(): array
    {
        return array_reverse($this->allNames($this->throwable));
    }

    /**
     * Retrieve a selected code snippet surrounding the line where the throwable occurred.
     *
     * @param int $around The number of lines to include before and after the line of the throwable (default: 4)
     * @return string The selected code snippet as a string
     */
    public function code(int $around = 4): string
    {
        return $this->codeSnippet(
            $this->throwable->getFile(),
            $this->throwable->getLine(),
            $around
        );
    }

    /**
     * Retrieve a selected code snippet surrounding the line where the first stack trace
     * file which class name starts with the provided namespace
     *
     * @param int $around The number of lines to include before and after the line of the
     *                    throwable (default: 4)
     * @return string The selected code snippet as a string
     */
    public function codeOfFirstAppFile(int $around = 4): string
    {
        $file = $this->throwable->getFile();
        $line = $this->throwable->getLine();

        /** @var array{function: string, line: int, file: string, class: class-string} $traceEntry */
        foreach ($this->throwable->getTrace() as $traceEntry) {
            if (str_starts_with($traceEntry['file'], $this->applicationRoot)) {
                $file = $traceEntry['file'];
                $line = $traceEntry['line'];
                break;
            }
        }

        return $this->codeSnippet($file, $line, $around);
    }

    /**
     * Retrieve a selected code snippet surrounding the line from provided file
     *
     * @param string $file The file to retrieve the code from
     * @param int $line The line where the error occurs
     * @param int $around The number of lines to include before and after the line of the throwable (default: 4)
     * @return string The selected code snippet as a string
     */
    private function codeSnippet(string $file, int $line = 1, int $around = 4): string
    {
        $code = "";
        $lines = file($file);

        if (is_array($lines)) {
            $start = ($line - 1) - $around;
            $end = (max($start, 0)) + $around + 1;
            $selected = array_slice($lines, $start, $end - $start);
            $code = implode("\n", $selected);
        }

        return $code;
    }

    /**
     * Parse the name of the throwable.
     *
     * @param Throwable $throwable The throwable object
     * @return string The parsed name of the throwable
     */
    private function parseName(Throwable $throwable): string
    {
        $parts = explode('\\', get_class($throwable));
        return trim(end($parts), '\\');
    }

    /**
     * Retrieve all names of the throwables in the chain.
     *
     * @param Throwable $throwable The current throwable
     * @return array<string> An array of names of throwables
     */
    private function allNames(Throwable $throwable): array
    {
        $names = [$this->parseName($throwable)];
        if ($throwable->getPrevious() instanceof Throwable) {
            $names = array_merge($names, $this->allNames($throwable->getPrevious()));
        }
        return $names;
    }
}
