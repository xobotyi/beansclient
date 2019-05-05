<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Command\WatchTube;
use xobotyi\beansclient\Exception\CommandException;

class WatchTubeTest extends TestCase
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

    public function testWatchTube() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("WATCHING 123", "WATCHING 123");

        $client = new BeansClient($conn);

        $client->watchTube('test1');
        self::assertEquals(123, $client->dispatchCommand(new WatchTube('test1')));
    }

    // test if response has data in

    public function testWatchTubeException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->watchTube('test1');
    }

    // test if tube name is empty

    public function testWatchTubeException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->watchTube('test1');
    }

    public function testWatchTubeException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("WATCHING 123"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->watchTube('   ');
    }
}
