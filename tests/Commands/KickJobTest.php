<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;

class KickJobTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public
    function testKickJob(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("KICKED", "NOT_FOUND");

        $client = new BeansClient($conn);

        self::assertEquals(true, $client->kickJob(1));
        self::assertEquals(false, $client->kickJob(2));
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

    // test if response has data in

    public
    function testKickJobException1(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kickJob(1);
    }

    // test if job id <=0

    public
    function testKickJobException2(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kickJob(1);
    }

    public
    function testKickJobException3(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kickJob(0);
    }
}
