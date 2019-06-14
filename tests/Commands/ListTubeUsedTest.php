<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;

class ListTubeUsedTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public
    function testListTubeUsed(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("USING test1"));

        $client = new BeansClientOld($conn);

        self::assertEquals('test1', $client->listTubeUsed());
    }

    // test if tube name in response is missing

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

    // test if response has wrong status name

    public
    function testListTubeUsedException1(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("USING"));

        $client = new BeansClientOld($conn);

        $this->expectException(CommandException::class);
        $client->listTubeUsed();
    }

    // test if response has data in

    public
    function testListTubeUsedException2(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClientOld($conn);

        $this->expectException(CommandException::class);
        $client->listTubeUsed();
    }

    public
    function testListTubeUsedException3(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClientOld($conn);

        $this->expectException(CommandException::class);
        $client->listTubeUsed();
    }
}
