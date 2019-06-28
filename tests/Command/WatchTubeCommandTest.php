<?php

namespace xobotyi\beansclient\Command;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class WatchTubeCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new WatchTubeCommand('testTube');

        $this->assertEquals($command->getCommandName(), CommandInterface::WATCH);
        $this->assertEquals($command->getArguments(), ['testTube']);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue('testTube'))
               ->with($this->isInstanceOf(WatchTubeCommand::class));

        $client->watchTube('testTube');
    }

    public
    function testCorrectResponse() {
        $command = new WatchTubeCommand('testTube');

        $this->assertEquals($command->processResponse([Response::WATCHING, 'testTube']), 'testTube');
    }

    public
    function testErrorTubeName() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Tube name has to be a valuable string");

        new WatchTubeCommand('   ');
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new WatchTubeCommand('testTube');
        $command->processResponse([Response::OUT_OF_MEMORY, 'testTube']);
    }
}
