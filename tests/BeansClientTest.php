<?php


namespace xobotyi\beansclient;

include_once __DIR__ . "/Command/rollup.php";

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Command\ListTubesCommand;
use xobotyi\beansclient\Exception\ClientException;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class BeansClientTest extends TestCase
{
    public
    function testConstruction() {
        $conn       = getConnectionMock($this,true);
        $serializer = new JsonSerializer();

        $client = new BeansClient($conn, $serializer);

        $this->assertEquals($client->getConnection(), $conn);
        $this->assertEquals($client->getSerializer(), $serializer);
    }

    public
    function testInactiveConnectionException(): void {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Unable to set inactive connection");

        new BeansClient(getConnectionMock($this,false));
    }

    public
    function testInactiveConnectionCommandDispatchException() {
        $conn = $this->getMockBuilder(Connection::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $conn->expects($this->at(0))
             ->method('isActive')
             ->will($this->returnValue(true));

        $client = new BeansClient($conn);

        $conn->expects($this->at(0))
             ->method('isActive')
             ->will($this->returnValue(false));

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Unable to dispatch command, connection is not active");

        $client->dispatchCommand(new ListTubesCommand());
    }

    public
    function testEmptyCommandResponseException() {
        $conn    = getConnectionMock($this,true);
        $client  = new BeansClient($conn);
        $command = new ListTubesCommand();

        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("");

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got nothing in response to `%s`", (string)$command));

        $client->dispatchCommand($command);
    }

    public
    function testErrorResponseException() {
        $conn    = getConnectionMock($this,true);
        $client  = new BeansClient($conn);
        $command = new ListTubesCommand();

        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("DRAINING 1 9");

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got error `%s` in response to `%s`", "DRAINING", (string)$command));

        $client->dispatchCommand($command);
    }

    public
    function testExceptionMissingDataLength() {
        $conn    = getConnectionMock($this,);
        $client  = new BeansClient($conn);
        $command = new ListTubesCommand();

        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("OK");

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(sprintf("Missing data length in response to `%s` [%s]",
                                              (string)$command,
                                              "OK"));

        $client->dispatchCommand($command);
    }

    public
    function testExceptionCrlfMissmatch() {
        $conn    = getConnectionMock($this,);
        $client  = new BeansClient($conn);
        $command = new ListTubesCommand();

        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("OK 5");
        $conn->method('read')
             ->withConsecutive([5])
             ->willReturnOnConsecutiveCalls("1\n2\n3", "\n\n");

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(sprintf('Expected CRLF (%s) after %u byte(s) of data, got `%s`',
                                              str_replace(["\r", "\n", "\t"],
                                                          ["\\r", "\\n", "\\t",],
                                                          BeansClient::CRLF),
                                              5,
                                              str_replace(["\r", "\n", "\t"],
                                                          ["\\r", "\\n", "\\t"],
                                                          "\n\n")));

        $client->dispatchCommand($command);
    }
}
