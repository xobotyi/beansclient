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

class ReleaseTest extends TestCase
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

    // test if response has data

    public function testListTubeUsedException1() :void {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("OK 9"));
        $conn->method('read')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("---\r\n12", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->release(13);
    }

    // test if response has wrong status name
    public function testListTubeUsedException2() :void {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->release(13);
    }

    // test if priority is not a number

    public function testRelease() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("RELEASED", "BURIED", "NOT_FOUND");

        $client = new BeansClient($conn);

        self::assertEquals('RELEASED', $client->release(123));
        self::assertEquals('BURIED', $client->release(13));
        self::assertEquals(null, $client->release(13));
    }

    // test if priority is less than 0

    public function testreleaseException3() {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("RELEASED"));
        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        self::assertEquals([], $client->release(13, ''));
    }

    // test if delay id less than 0

    public function testreleaseException4() {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("RELEASED"));
        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        self::assertEquals([], $client->release(13, -1));
    }

    // test if priority is too big

    public function testreleaseException5() {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("RELEASED"));
        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        self::assertEquals([], $client->release(13, 0, -1));
    }

    // test if priority is less than 0

    public function testreleaseException7() {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("RELEASED"));
        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        self::assertEquals([], $client->release(13, Put::MAX_PRIORITY + 1));
    }

    public function testreleaseException8() {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("RELEASED"));
        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        self::assertEquals([], $client->release(-1));
    }
}