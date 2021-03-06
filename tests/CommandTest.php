<?php


namespace xobotyi\beansclient;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class CommandTest extends TestCase
{
    public
    function testConstruction() {
        $command = new Command("test");

        $this->assertNull($command->getSerializer());
        $this->assertNull($command->getPayload());
        $this->assertNull($command->getPayload());
        $this->assertEmpty($command->getArguments());
    }

    public
    function testBuiltCommand() {
        $command = new Command("test", null, [1, 2, 4], "Hello world");

        $toBe = "test 1 2 4 11\r\nHello world";

        $this->assertEquals(
            $command->buildCommand(),
            $toBe
        );
        $this->assertEquals(
            (string)$command,
            $toBe
        );
    }

    public
    function testPayloadSerialization() {
        $command = new Command("test", new JsonSerializer(), null, "Hello world");

        $this->assertTrue($command->hasPayload());
        $this->assertEquals($command->getRawPayload(), "Hello world");

        $this->assertEquals($command->getPayload(), '"Hello world"');

        $command->setPayload(["Hello", "world"]);
        $this->assertEquals($command->getRawPayload(), ["Hello", "world"]);

        $this->assertEquals($command->getPayload(), '["Hello","world"]');
    }

    public
    function testExceptionArrayPayloadWithoutSerializer() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("No serializer provided, payload has to be a string or a number. Configure serializer or cast payload to the string manually.");

        new Command("test", null, null, ["Hello", "world"]);
    }

    public
    function testExceptionPayloadTooBig() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(
            sprintf("Maximum payload size is `%s` bytes, got `%s`.", Command::MAX_PAYLOAD_SIZE, Command::MAX_PAYLOAD_SIZE + 25)
        );

        new Command("test", null, null, random_bytes(Command::MAX_PAYLOAD_SIZE + 25));
    }
}
