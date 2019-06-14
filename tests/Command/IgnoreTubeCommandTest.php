<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class IgnoreTubeCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new IgnoreTubeCommand("testTube");

        $this->assertEquals($command->getCommandName(), CommandInterface::IGNORE);
        $this->assertEquals($command->getArguments(), ["testTube"]);
    }

    public
    function testCorrectResponse() {
        $command = new IgnoreTubeCommand('testTube');

        $this->assertEquals($command->processResponse([Response::WATCHING, 5]), 5);
        $this->assertNull($command->processResponse([Response::NOT_IGNORED]));
    }

    public
    function testErrorTubeName() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Tube name has to be a valuable string");

        new IgnoreTubeCommand('   ');
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new IgnoreTubeCommand('testTube');
        $command->processResponse([Response::OUT_OF_MEMORY, 'testTube']);
    }
}