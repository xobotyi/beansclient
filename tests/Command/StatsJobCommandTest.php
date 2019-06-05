<?php

namespace xobotyi\beansclient\Command;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class StatsJobCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new StatsJobCommand(1);

        $this->assertEquals($command->getCommandName(), CommandInterface::STATS_JOB);
        $this->assertEquals($command->getArguments(), [1]);
    }

    public
    function testCorrectResponse() {
        $command = new StatsJobCommand(1);

        $this->assertNull($command->processResponse([Response::NOT_FOUND]));
        $this->assertEquals($command->processResponse([Response::OK], "a: b\nc: d"), ["a" => "b", "c" => "d"]);
    }

    public
    function testErrorJobId() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new StatsJobCommand(0);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new StatsJobCommand(1);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testEmptyDataResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Expected response body, got `%s`", null));

        $command = new StatsJobCommand(1);
        $command->processResponse([Response::OK]);
    }
}