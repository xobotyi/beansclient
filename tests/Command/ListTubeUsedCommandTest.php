<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class ListTubeUsedCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new ListTubeUsedCommand();

        $this->assertEquals($command->getCommandName(), CommandInterface::LIST_TUBE_USED);
        $this->assertEquals($command->getArguments(), []);
    }

    public
    function testCorrectResponse() {
        $command = new ListTubeUsedCommand();

        $this->assertEquals($command->processResponse([Response::OK], "a: b\nc: d"), ["a: b", "c: d"]);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new ListTubeUsedCommand();
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testEmptyDataResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Expected response body, got `%s`", null));

        $command = new ListTubeUsedCommand();
        $command->processResponse([Response::OK]);
    }
}