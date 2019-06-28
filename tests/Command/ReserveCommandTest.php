<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;
use xobotyi\beansclient\Serializer\JsonSerializer;

class ReserveCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new ReserveCommand();

        $this->assertEquals($command->getCommandName(), CommandInterface::RESERVE);
        $this->assertEquals($command->getArguments(), []);

        $command = new ReserveCommand(3);

        $this->assertEquals($command->getCommandName(), CommandInterface::RESERVE_WITH_TIMEOUT);
        $this->assertEquals($command->getArguments(), [3]);
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->atLeastOnce())
               ->method('dispatchCommand')
               ->will($this->onConsecutiveCalls(['id' => 2, 'payload' => null], null))
               ->with($this->isInstanceOf(ReserveCommand::class));

        $client->reserve(2);
        $client->reserve(2);
    }

    public
    function testCorrectResponse() {
        $command = new ReserveCommand();

        $this->assertNull($command->processResponse([Response::TIMED_OUT]));
        $this->assertFalse($command->processResponse([Response::DEADLINE_SOON]));
        $this->assertEquals($command->processResponse([Response::RESERVED, '23']), ['id' => 23, 'payload' => null]);
    }

    public
    function testSerializedResponse() {
        $ser     = new JsonSerializer();
        $command = new ReserveCommand(0, $ser);
        $data    = ["Hello", "World" => "true"];

        $this->assertEquals($command->processResponse([Response::RESERVED, '23'], $ser->serialize($data)), ['id' => 23, 'payload' => $data]);
    }

    public
    function testErrorInvalidTimeout() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Timeout has to be >= 0");

        new ReserveCommand(-1);
    }

    public
    function testErrorResponse() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new ReserveCommand();
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }
}
