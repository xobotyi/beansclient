<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class PauseCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new PauseCommand("testTube", 123);

        $this->assertEquals($command->getCommandName(), CommandInterface::PAUSE_TUBE);
        $this->assertEquals($command->getArguments(), ["testTube", 123]);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue(true))
               ->with($this->isInstanceOf(PauseCommand::class));

        $client->pause("testTube", 123);
    }

    public
    function testCorrectResponse() {
        $command = new PauseCommand("testTube", 123);

        $this->assertTrue($command->processResponse([Response::PAUSED]));
        $this->assertFalse($command->processResponse([Response::NOT_FOUND]));
    }

    public
    function testErrorTubeName() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Tube name has to be a valuable string");

        new PauseCommand('   ', 1);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new PauseCommand('testTube', 1);
        $command->processResponse([Response::OUT_OF_MEMORY, 'testTube']);
    }

    public
    function testErrorInvalidDelay0() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Delay has to be a number, got %s', "string"));

        new PauseCommand("testTube", "hey!");
    }

    public
    function testErrorInvalidDelay1() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Delay has to be >= %d, got %d', 0, -1));

        new PauseCommand("testTube", -1);
    }

    public
    function testErrorInvalidDelay2() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Delay has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, CommandInterface::PRIORITY_MAXIMUM + 1));

        new PauseCommand("testTube", CommandInterface::PRIORITY_MAXIMUM + 1);
    }
}
