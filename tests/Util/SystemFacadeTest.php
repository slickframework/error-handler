<?php
/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\ErrorHandler\Util;

use PHPUnit\Runner\ErrorHandler;
use Slick\ErrorHandler\Util\SystemFacade;
use PHPUnit\Framework\TestCase;

class SystemFacadeTest extends TestCase
{

    public function testOutputControl(): void
    {
        $sys = new SystemFacade();
        $sys->startOutputBuffering();
        echo "test";
        $this->assertEquals("test", $sys->cleanOutputBuffer());
    }

    public function testOutputBufferLever(): void
    {
        $sys = new SystemFacade();
        $this->assertEquals(ob_get_level(), $sys->getOutputBufferLevel());
    }

    public function testFlush()
    {
        $sys = new SystemFacade();
        $sys->startOutputBuffering();
        echo "test";
        $sys->flushOutputBuffer();
        $this->assertEquals("test", $sys->cleanOutputBuffer());
    }

    public function testEndOutputBuffering()
    {
        $sys = new SystemFacade();
        $sys->startOutputBuffering();
        echo "test";
        $this->assertEquals("test", $sys->cleanOutputBuffer());
        $sys->startOutputBuffering();
        $this->assertTrue($sys->endOutputBuffering());
    }

    public function testLastError(): void
    {
        $sys = new SystemFacade();
        $this->assertNull($sys->getLastError());
    }

    public function testGetErrorReportingLevel(): void
    {
        $sys = new SystemFacade();
        $this->assertEquals(error_reporting(), $sys->getErrorReportingLevel());
    }

    public function testSetErrorHandler(): void
    {
        $sys = new SystemFacade();
        $callback = fn($x) => $x + 1;
        $this->assertInstanceOf(ErrorHandler::class, $sys->setErrorHandler($callback));
        $this->assertTrue($sys->restoreErrorHandler());
    }

    public function testSetExceptionHandler(): void
    {
        $sys = new SystemFacade();
        $callback = fn($x) => $x + 1;
        $this->assertNull($sys->setExceptionHandler($callback));
        $this->assertTrue($sys->restoreExceptionHandler());
    }

    public function testSendHeaders()
    {
        $sys = new SystemFacade();
        $this->assertTrue($sys->sendHeaders(['Content-Type' => 'text/html']));
    }

    public function testSetHttpResponseCode(): void
    {
        $sys = new SystemFacade();
        $this->assertTrue($sys->setHttpResponseCode(500));
    }
}
