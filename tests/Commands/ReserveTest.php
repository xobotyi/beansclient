<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class ReserveTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public
    function testReserve(): void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("TIMED_OUT", "RESERVED 1 9", "RESERVED 1 9");
        $conn->method('read')
             ->withConsecutive([9], [2], [9], [2])
             ->willReturnOnConsecutiveCalls("[1,2,3,4]", "\r\n", "[1,2,3,4]", "\r\n");

        $client = new BeansClient($conn);
        self::assertEquals(null, $client->reserve()->id);
        self::assertEquals(1, $client->reserve()->id);

        $client = new BeansClient($conn, new JsonSerializer());
        self::assertEquals([1, 2, 3, 4], $client->reserve()->payload);
    }

    // test if response has wrong status name

    private
    function getConnection(bool $active = true) {
        $conn = $this->getMockBuilder(Connection::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $conn->expects($this->any())
             ->method('isActive')
             ->will($this->returnValue($active));

        return $conn;
    }

    // test if response has no data in

    public
    function testReserveException1(): void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->reserve();
    }

    // test if timeout < 0

    public
    function testReserveException2(): void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 0"));

        $conn->method('read')
             ->withConsecutive([0], [2])
             ->willReturnOnConsecutiveCalls("", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->reserve();
    }

    public
    function testReserveException3(): void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("TOUCHED"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->reserve(-1);
    }
}
