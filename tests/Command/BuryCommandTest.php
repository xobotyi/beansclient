<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class BuryCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new BuryCommand(1, 2);

        $this->assertEquals($command->getCommandName(), CommandInterface::BURY);
        $this->assertEquals($command->getArguments(), [1, 2]);
    }

    public
    function testCorrectResponse() {
        $command = new BuryCommand(1, 2);

        $this->assertFalse($command->processResponse([Response::NOT_FOUND]));
        $this->assertTrue($command->processResponse([Response::BURIED]));
    }

    public
    function testErrorInvalidJobId() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new BuryCommand(0, 1);
    }

    public
    function testErrorInvalidPriority0() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be a number, got %s', "string"));

        new BuryCommand(1, "hey!");
    }

    public
    function testErrorInvalidPriority1() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, -1));

        new BuryCommand(1, -1);
    }

    public
    function testErrorInvalidPriority2() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, CommandInterface::PRIORITY_MAXIMUM + 1));

        new BuryCommand(1, CommandInterface::PRIORITY_MAXIMUM + 1);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new BuryCommand(1, 1);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }
}