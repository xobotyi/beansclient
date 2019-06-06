<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class ReleaseCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new ReleaseCommand(1, 2, 3);

        $this->assertEquals($command->getCommandName(), CommandInterface::RELEASE);
        $this->assertEquals($command->getArguments(), [1, 2, 3]);
    }

    public
    function testCorrectResponse() {
        $command = new ReleaseCommand(1, 2, 3);

        $this->assertNull($command->processResponse([Response::NOT_FOUND]));
        $this->assertEquals($command->processResponse([Response::RELEASED]), Response::RELEASED);
        $this->assertEquals($command->processResponse([Response::BURIED]), Response::BURIED);
    }

    public
    function testErrorInvalidJobId() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new ReleaseCommand(0, 1, 1);
    }

    public
    function testErrorInvalidDelay() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Release delay has to be >= 0");

        new ReleaseCommand(1, 1, -3);
    }

    public
    function testErrorInvalidPriority0() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to numeric, got %s', "string"));

        new ReleaseCommand(1, "hey!", 1);
    }

    public
    function testErrorInvalidPriority1() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, -1));

        new ReleaseCommand(1, -1, 1);
    }

    public
    function testErrorInvalidPriority2() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, CommandInterface::PRIORITY_MAXIMUM + 1));

        new ReleaseCommand(1, CommandInterface::PRIORITY_MAXIMUM + 1, 1);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new ReleaseCommand(1, 1, 1);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }
}