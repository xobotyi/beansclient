<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class KickJobCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new KickJobCommand(2);

        $this->assertEquals($command->getCommandName(), CommandInterface::KICK_JOB);
        $this->assertEquals($command->getArguments(), [2]);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue(true))
               ->with($this->isInstanceOf(KickJobCommand::class));

        $client->kickJob(3);
    }

    public
    function testCorrectResponse() {
        $command = new KickJobCommand(2);

        $this->assertTrue($command->processResponse([Response::KICKED]));
        $this->assertFalse($command->processResponse([Response::NOT_FOUND]));
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new KickJobCommand(2);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testErrorInvalidCount() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new KickJobCommand(0);
    }
}
