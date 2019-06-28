<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class ListTubesCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new ListTubesCommand();

        $this->assertEquals($command->getCommandName(), CommandInterface::LIST_TUBES);
        $this->assertEquals($command->getArguments(), []);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue([]))
               ->with($this->isInstanceOf(ListTubesCommand::class));

        $client->listTubes();
    }

    public
    function testCorrectResponse() {
        $command = new ListTubesCommand();

        $this->assertEquals($command->processResponse([Response::OK], "a: b\nc: d"), ["a: b", "c: d"]);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new ListTubesCommand();
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testEmptyDataResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Expected response body, got `%s`", null));

        $command = new ListTubesCommand();
        $command->processResponse([Response::OK]);
    }
}
