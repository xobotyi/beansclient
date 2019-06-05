<?php

namespace xobotyi\beansclient\Command;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class TouchCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new TouchCommand(1);

        $this->assertEquals($command->getCommandName(), CommandInterface::TOUCH);
        $this->assertEquals($command->getArguments(), [1]);
    }

    public
    function testCorrectResponse() {
        $command = new TouchCommand(1);

        $this->assertTrue($command->processResponse([Response::TOUCHED]));
        $this->assertFalse($command->processResponse([Response::NOT_FOUND]));
    }

    public
    function testErrorJobId() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new TouchCommand(0);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new TouchCommand(1);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }
}