<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Command\Put;
use xobotyi\beansclient\Exception\Command;

class BuryTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    private function getConnection(bool $active = true) {
        $conn = $this->getMockBuilder('\xobotyi\beansclient\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();

        $conn->expects($this->any())
             ->method('isActive')
             ->will($this->returnValue($active));

        return $conn;
    }

    // test if response has wrong status name

    public function testBury() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("BURIED", "NOT_FOUND");

        $client = new BeansClient($conn);

        self::assertEquals(true, $client->bury(1));
        self::assertEquals(false, $client->bury(2));
    }

    // test if response has data in

    public function testBuryException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(1);
    }

    // test if job id <=0

    public function testBuryException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(1);
    }

    // test if priority not number

    public function testBuryException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(0);
    }

    // test if priority less than 0

    public function testBuryException4() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(1, '');
    }

    // test if priority greater than maximal allowed

    public function testBuryException5() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(1, -1);
    }

    public function testBuryException6() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->bury(1, Put::MAX_PRIORITY + 1);
    }
}