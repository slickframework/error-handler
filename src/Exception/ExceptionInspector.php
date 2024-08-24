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
    private string $firstAppFile = '';
    private int $lineOfFirstAppFile = 0;

    /**
     * @var array<string, array{statusCode: int, help: string}>
     */
    private array $errorsDb = [];

    private int $statusCode = 500;

    private ?string $help = null;

    /**
     * @var array<object{code: int, description: string}>
     */
    private array $httpCodes = [];

    public function __construct(private readonly Throwable $throwable, private readonly string $applicationRoot = '')
    {
        $this->readFirstAppFile();
        $errorsDbFile = dirname(__DIR__, 2) . '/config/errors_db.php';
        $httpCodesFile = dirname(__DIR__, 2) . '/config/http_codes.json';
        $this->errorsDb = include $errorsDbFile;
        $this->loadDb();
        $this->httpCodes = json_decode(file_get_contents($httpCodesFile));
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

    public function line(): int
    {
        return $this->throwable->getLine();
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
        return $this->codeSnippet($this->firstAppFile, $this->lineOfFirstAppFile, $around);
    }

    public function firstAppFile(): string
    {
        return $this->firstAppFile;
    }

    public function lineOfFirstAppFile(): int
    {
        return $this->lineOfFirstAppFile;
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
            $selected = array_slice($lines, $start, (2*$around) + 1);
            $code = implode("", $selected);
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

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    private function readFirstAppFile(): void
    {
        $file = $this->throwable->getFile();
        $line = $this->throwable->getLine();

        /** @var array{function: string, line: int, file: string, class: class-string} $traceEntry */
        foreach ($this->throwable->getTrace() as $traceEntry) {
            if (!array_key_exists('file', $traceEntry) || !is_string($traceEntry['file'])) {
                continue;
            }

            if (str_starts_with($traceEntry['file'], $this->applicationRoot)) {
                $file = $traceEntry['file'];
                $line = $traceEntry['line'];
                break;
            }
        }

        $this->firstAppFile = $file;
        $this->lineOfFirstAppFile = $line;
    }

    private function loadDb(): void
    {
        foreach ($this->errorsDb as $className => $data) {
            if (!is_a($this->throwable, $className)) {
                continue;
            }

            $this->statusCode = $data["statusCode"];
            $this->help = $data["help"];
            break;
        }
    }

    public function help(): ?string
    {
        if (!$this->help) {
            return null;
        }

        $search = [
            '%path' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : ''
        ];
        return str_replace(array_keys($search), array_values($search), $this->help);
    }

    public function httpError(?int $statusCode = null): string
    {
        $statusCode = $statusCode ?? $this->statusCode();
        foreach ($this->httpCodes as $httpCode) {
            if ($httpCode->code === $statusCode) {
                return $httpCode->description;
            }
        }
        return '';
    }
}
