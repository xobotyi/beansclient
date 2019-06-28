<?php

namespace xobotyi\beansclient\Command;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class UseTubeCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new UseTubeCommand('testTube');

        $this->assertEquals($command->getCommandName(), CommandInterface::USE);
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
               ->with($this->isInstanceOf(UseTubeCommand::class));

        $client->useTube('testTube');
    }

    public
    function testClientErrorTubeName() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue('testTube2'))
               ->with($this->isInstanceOf(UseTubeCommand::class));


        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Failed to use `%s` tube, using `%s` instead", 'testTube', 'testTube2'));

        $client->useTube('testTube');
    }

    public
    function testCorrectResponse() {
        $command = new UseTubeCommand('testTube');

        $this->assertEquals($command->processResponse([Response::USING, 'testTube']), 'testTube');
    }

    public
    function testErrorTubeName() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Tube name has to be a valuable string");

        new UseTubeCommand('   ');
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new UseTubeCommand('testTube');
        $command->processResponse([Response::OUT_OF_MEMORY, 'testTube']);
    }
}
