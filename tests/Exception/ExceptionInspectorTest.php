<?php
/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\ErrorHandler\Exception;

use Slick\ErrorHandler\Exception\ExceptionInspector;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;

class ExceptionInspectorTest extends TestCase
{

    public function testExceptionsPath()
    {
        $inspector = new ExceptionInspector(new \Exception(message: 'test', previous: new \ErrorException('test', 1)));
        $this->assertEquals(['ErrorException', 'Exception'], $inspector->exceptionsPath());
    }

    public function testExceptionName()
    {
        $inspector = new ExceptionInspector(new \Exception('test'));
        $this->assertEquals('Exception', $inspector->exceptionName());
    }

    public function testInitializable()
    {
        $inspector = new ExceptionInspector(new \Exception('test'));
        $this->assertInstanceOf(ExceptionInspector::class, $inspector);
    }

    public function testCodeSnippetThrowable(): void
    {
        try {
            throw new \Exception('Error');
        } catch (\Throwable $error) {
            $inspector = new ExceptionInspector($error);
            $codeSnippet = $inspector->code(5);
            $this->assertStringContainsString('public function testCodeSnippetThrowable(): void', $codeSnippet);
        }
    }

    public function testCodeSnippetAppTrace(): void
    {
        try {
            throw new \Exception('Error');
        } catch (\Throwable $error) {
            $inspector = new ExceptionInspector($error, dirname(__DIR__, 2).'/vendor/phpunit');
            $codeSnippet = $inspector->codeOfFirstAppFile(7);
            $this->assertStringContainsString('final protected function runTest(): mixed', $codeSnippet);
        }
    }

    public function testHelpData(): void
    {
        try {
            throw new LoaderError('Error');
        } catch (\Throwable $error) {
            $inspector = new ExceptionInspector($error);
            $this->assertEquals(500, $inspector->statusCode());
            $this->assertStringContainsString(
                'Template engine looks for templates in specific directories',
                $inspector->help()
            );
            $this->assertEquals('Internal Server Error', $inspector->httpError());
            $this->assertEquals('Already Reported', $inspector->httpError(208));
        }
    }
}
