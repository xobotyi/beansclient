<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\Command;

class StatsTubeTest extends TestCase
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

    public function testStatsTube() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("OK 25", 'NOT_FOUND');

        $conn->method('read')
             ->withConsecutive([25], [2])
             ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1\r\njobs: 25\r\nrequests: 100", "\r\n");

        $client = new BeansClient($conn);

        self::assertEquals(['default', 'test1', 'jobs' => 25, 'requests' => 100], $client->statsTube('test'));
        self::assertEquals(null, $client->statsTube('test'));
    }

    // test if response has no data in

    public function testStatsTubeException1() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("SOME_STUFF"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->statsTube('test');
    }

    // test if tube name is empty

    public function testStatsTubeException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("OK 0"));

        $conn->method('read')
             ->withConsecutive([0], [2])
             ->willReturnOnConsecutiveCalls("", "\r\n");

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->statsTube('test');
    }

    public function testStatsTubeException3() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->will($this->returnValue("BURIED"));

        $client = new BeansClient($conn);

        $this->expectException(Command::class);
        $client->statsTube('   ');
    }
}
