<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\Command;

class ListTubeUsedTest extends TestCase
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

    // test if tube name in response is missing

    public function testListTubeUsed() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("USING test1"));

        $client = new BeansClient($conn);

        self::assertEquals('test1', $client->listTubeUsed());
    }

    // test if response has wrong status name

    public function testListTubeUsedException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("USING"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->listTubeUsed();
    }

    // test if response has data in

    public function testListTubeUsedException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->listTubeUsed();
    }

    public function testListTubeUsedException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 25"));

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->listTubeUsed();
    }
}
