<?php
/**
 * This file is part of error-handler
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\ErrorHandler;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Slick\ErrorHandler\Exception\ExceptionInspector;
use Slick\ErrorHandler\Handler\HandlerInterface;
use Slick\ErrorHandler\Runner;
use PHPUnit\Framework\TestCase;
use Slick\ErrorHandler\RunnerInterface;
use Slick\ErrorHandler\Util\SystemFacade;

class RunnerTest extends TestCase
{
    use ProphecyTrait;

    public function testInitializable(): void
    {
        $system = $this->prophesize(SystemFacade::class)->reveal();
        $runner = new Runner($system);
        $this->assertInstanceOf(Runner::class, $runner);
    }

    public function testUnregister()
    {
        $system = $this->prophesize(SystemFacade::class);
        $runner = new Runner($system->reveal());
        $system->restoreErrorHandler()->shouldBeCalled();
        $system->restoreExceptionHandler()->shouldBeCalled();
        $this->assertSame($runner, $runner->unregister());
    }

    public function testHandleErrorByPass(): void
    {
        $system = $this->prophesize(SystemFacade::class);
        $system->getErrorReportingLevel()->willReturn(0);
        $runner = new Runner($system->reveal());
        $this->assertFalse($runner->handleError(E_NOTICE, 'Some error', __FILE__, __LINE__));
    }

    public function testHandleError(): void
    {
        $system = $this->prophesize(SystemFacade::class);
        $system->getErrorReportingLevel()->willReturn(E_ALL);
        $system->startOutputBuffering()->willReturn(true);
        $system->cleanOutputBuffer()->willReturn('Error output');
        $system->getOutputBufferLevel()->willReturn(1, 0);
        $system->endOutputBuffering()->willReturn(true);


        $handler = $this->prophesize(HandlerInterface::class);
        $runner = new Runner($system->reveal());
        $handler->handle(
            Argument::type(\ErrorException::class),
            Argument::type(ExceptionInspector::class),
            $runner
        )
            ->shouldBeCalled()
            ->willReturn(HandlerInterface::DONE);
        $runner->pushHandler($handler->reveal());
        $this->assertTrue($runner->handleError(E_NOTICE, 'Some error', __FILE__, __LINE__));
    }

    public function testGetHandlers(): void
    {
        $system = $this->prophesize(SystemFacade::class)->reveal();
        $runner = new Runner($system);
        $this->assertEmpty($runner->getHandlers());
    }


    public function testHandleException(): void
    {
        $system = $this->prophesize(SystemFacade::class);
        $system->getErrorReportingLevel()->willReturn(E_ALL);
        $system->startOutputBuffering()->willReturn(true);
        $system->cleanOutputBuffer()->willReturn('Error output');
        $system->getOutputBufferLevel()->willReturn(1, 0);
        $system->endOutputBuffering()->willReturn(true);
        $system->flushOutputBuffer()->shouldBeCalled();
        $system->stopExecution(1)->shouldBeCalled();

        $handler = function (\Throwable $throwable, ExceptionInspector $inspector, RunnerInterface $run): int {
            return HandlerInterface::QUIT;
        };

        $runner = new Runner($system->reveal());
        $runner->pushHandler($handler);
        ob_start();
        $this->assertTrue($runner->handleError(E_NOTICE, 'Some error', __FILE__, __LINE__));
        ob_end_clean();
    }

    public function testHandleShutdown(): void
    {
        $system = $this->prophesize(SystemFacade::class);
        $error = ['type' => E_ERROR, 'message' => 'Some error', 'file' => __FILE__, 'line' => __LINE__];
        $system->getLastError()->willReturn($error);
        $system->getErrorReportingLevel()->willReturn(E_ALL);
        $system->startOutputBuffering()->willReturn(true);
        $system->cleanOutputBuffer()->willReturn('Error output');
        $system->getOutputBufferLevel()->willReturn(1, 0);
        $system->endOutputBuffering()->willReturn(true);

        $handler = $this->prophesize(HandlerInterface::class);
        $runner = new Runner($system->reveal());
        $handler->handle(
            Argument::type(\ErrorException::class),
            Argument::type(ExceptionInspector::class),
            $runner
        )
            ->shouldBeCalled()
            ->willReturn(HandlerInterface::DONE);
        $runner->pushHandler($handler->reveal());
        $runner->handleShutdown();
    }

    public function testPushHandler(): void
    {
        $system = $this->prophesize(SystemFacade::class)->reveal();
        $handler = $this->prophesize(HandlerInterface::class)->reveal();

        $runner = new Runner($system);
        $this->assertSame($runner, $runner->pushHandler($handler));
        $this->assertEquals([$handler], $runner->getHandlers());
    }

    public function testClearHandlers(): void
    {
        $system = $this->prophesize(SystemFacade::class)->reveal();
        $handler = $this->prophesize(HandlerInterface::class)->reveal();

        $runner = new Runner($system);
        $runner->pushHandler($handler);
        $this->assertSame($runner, $runner->clearHandlers());
        $this->assertEmpty($runner->getHandlers());
    }

    public function testRegister()
    {
        $system = $this->prophesize(SystemFacade::class);
        $runner = new Runner($system->reveal());
        $system->setErrorHandler([$runner, RunnerInterface::ERROR_HANDLER])->shouldBeCalled();
        $system->setExceptionHandler([$runner, RunnerInterface::EXCEPTION_HANDLER])->shouldBeCalled();
        $system->registerShutdownFunction([$runner, RunnerInterface::SHUTDOWN_HANDLER])->shouldBeCalled();
        $this->assertSame($runner, $runner->register());
    }

    public function testSendHeaders(): void
    {
        $system = $this->prophesize(SystemFacade::class);
        $headers = ['x_foo' => 'bar'];
        $system->sendHeaders($headers)->shouldBeCalled()->willReturn(true);
        $runner = new Runner($system->reveal());
        $runner->outputHeaders($headers);
    }
}
