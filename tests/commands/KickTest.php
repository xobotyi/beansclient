<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;

class KickTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    private function getConnection(bool $active = true) {
        $conn = $this->getMockBuilder(Connection::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $conn->expects($this->any())
             ->method('isActive')
             ->will($this->returnValue($active));

        return $conn;
    }

    // test if response has wrong status name

    public function testKick() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("KICKED 3");

        $client = new BeansClient($conn);

        self::assertEquals(3, $client->kick(3));
    }

    // test if response has data in

    public function testKickException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kick(1);
    }

    // test if jobs count less or equal 0

    public function testKickException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kick(21);
    }

    public function testKickException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("KICKED 3"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->kick(0);
    }
}
