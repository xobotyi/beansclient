<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class KickCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new KickCommand(2);

        $this->assertEquals($command->getCommandName(), CommandInterface::KICK);
        $this->assertEquals($command->getArguments(), [2]);
    }

    public
    function testCorrectResponse() {
        $command = new KickCommand(2);

        $this->assertEquals($command->processResponse([Response::KICKED, 3]), 3);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new KickCommand(2);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testErrorInvalidCount() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Kick count has to be a positive integer");

        $command = new KickCommand(0);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }
}