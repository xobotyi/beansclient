<?php

namespace xobotyi\beansclient\Command;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class StatsTubeCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new StatsTubeCommand('testTube');

        $this->assertEquals($command->getCommandName(), CommandInterface::STATS_TUBE);
        $this->assertEquals($command->getArguments(), ['testTube']);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue([]))
               ->with($this->isInstanceOf(StatsTubeCommand::class));

        $client->statsTube('testTube');
    }

    public
    function testCorrectResponse() {
        $command = new StatsTubeCommand('testTube');

        $this->assertNull($command->processResponse([Response::NOT_FOUND]));
        $this->assertEquals($command->processResponse([Response::OK], "a: b\nc: d"), ["a" => "b", "c" => "d"]);
    }

    public
    function testErrorTubeName() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Tube name has to be a valuable string");

        new StatsTubeCommand('   ');
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new StatsTubeCommand('testTube');
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testEmptyDataResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Expected response body, got `%s`", null));

        $command = new StatsTubeCommand('testTube');
        $command->processResponse([Response::OK]);
    }
}
