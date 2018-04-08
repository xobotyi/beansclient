<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Command\UseTube;
use xobotyi\beansclient\Exception\Command;

class UseTubeTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public function testUseTube() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("USING test1", "USING test1");

        $client = new BeansClient($conn);

        $client->useTube('test1');
        self::assertEquals('test1', $client->dispatchCommand(new UseTube('test1')));
    }

    // test if response has another tube name
    public function testUseTubeException() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("USING test2");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->useTube('test1');
    }

    // test if response has wrong status name
    public function testUseTubeException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->useTube('test1');
    }

    // test if response has data in
    public function testUseTubeException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->useTube('test1');
    }

    // test if tube name is empty
    public function testUseTubeException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("WATCHING 123"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->useTube('   ');
    }

    private function getConnection(bool $active = true) {
        $conn = $this->getMockBuilder('\xobotyi\beansclient\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();

        $conn->expects($this->any())
             ->method('isActive')
             ->will($this->returnValue($active));

        return $conn;
    }
}