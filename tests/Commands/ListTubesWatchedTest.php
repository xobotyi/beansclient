<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;

class ListTubesWatchedTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public
    function testListTubesWatched(): void {
        $conn = $this->getConnection();

        $conn->method('readLine')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClientOld($conn);

        self::assertEquals(['default', 'test1'], $client->listTubesWatched());
    }

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
}
