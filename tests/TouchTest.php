<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;

class TouchTest extends TestCase
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

    public function testTouch() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("TOUCHED", "NOT_FOUND");

        $client = new BeansClient($conn);

        self::assertEquals(true, $client->touch(1));
        self::assertEquals(false, $client->touch(2));
    }

    // test if response has data in

    public function testTouchException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->touch(1);
    }

    // test if job id <=0

    public function testTouchException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->touch(1);
    }

    public function testTouchException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("TOUCHED"));

        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        $client->touch(0);
    }
}
