<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;
use xobotyi\beansclient\Serializer\JsonSerializer;

class PeekCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new PeekCommand(1);
        $this->assertEquals($command->getArguments(), [1]);
        $this->assertEquals($command->getCommandName(), CommandInterface::PEEK);

        $command = new PeekCommand(PeekCommand::TYPE_BURIED);
        $this->assertEquals($command->getArguments(), []);
        $this->assertEquals($command->getCommandName(), CommandInterface::PEEK_BURIED);

        $command = new PeekCommand(PeekCommand::TYPE_READY);
        $this->assertEquals($command->getArguments(), []);
        $this->assertEquals($command->getCommandName(), CommandInterface::PEEK_READY);

        $command = new PeekCommand(PeekCommand::TYPE_DELAYED);
        $this->assertEquals($command->getArguments(), []);
        $this->assertEquals($command->getCommandName(), CommandInterface::PEEK_DELAYED);
    }

    public
    function testCorrectResponse() {
        $command = new PeekCommand(1);

        $this->assertNull($command->processResponse([Response::NOT_FOUND]));
        $this->assertEquals($command->processResponse([Response::FOUND], "123"), "123");
    }

    public
    function testSerializedResponse() {
        $ser     = new JsonSerializer();
        $command = new PeekCommand(1, $ser);
        $data    = ["Hello", "World" => "true"];

        $this->assertEquals($command->processResponse([Response::FOUND], $ser->serialize($data)), $data);
    }

    public
    function testErrorInvalidId() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Job id must be a positive integer");

        new PeekCommand(0);
    }

    public
    function testErrorInvalidSubject() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Invalid peek subject `%s`', "HelloWorld"));

        new PeekCommand('HelloWorld');
    }

    public
    function testErrorUnexpectedStatus() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new PeekCommand(1);
        $command->processResponse([Response::OUT_OF_MEMORY]);
    }

    public
    function testErrorEmptyBody() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Expected response body, got `%s`", ""));

        $command = new PeekCommand(1);
        $command->processResponse([Response::FOUND], "");
    }
}