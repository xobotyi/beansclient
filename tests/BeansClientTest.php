<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\ClientException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class BeansClientTest extends TestCase
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

    public function testActiveConnectionException() :void {
        $connActive = $this->getConnection(true);
        $client     = new BeansClient($connActive);

        self::assertEquals($connActive, $client->getConnection());
    }

    public function testException() :void {
        $conn = $this->getConnection();
        $conn->method('readln')
             ->will($this->returnValue("OK"));

        $client = new BeansClient($conn);

        $this->expectException(ClientException::class);
        $client->release(13);
    }

    public function testException2() :void {
        $conn = $this->getConnection();

        $conn->method('readln')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("RESERVED 1 9");
        $conn->method('read')
             ->withConsecutive([9], [2], [9], [2])
             ->willReturnOnConsecutiveCalls("[1,2,3,4]", "  ");

        $client = new BeansClient($conn);

        $this->expectException(ClientException::class);
        $client->release(13);
    }

    // test if response suppose to have data, but has to content length header

    public function testGetters() :void {
        $conn       = $this->getConnection();
        $serializer = new JsonSerializer();

        $client = new BeansClient($conn, $serializer);

        self::assertEquals($conn, $client->getConnection());
        self::assertEquals($serializer, $client->getSerializer());
    }

    // test if response has no or incorrect CRLF after data

    public function testInactiveConnectionException1() :void {
        $connInactive = $this->getConnection(false);

        $this->expectException(ClientException::class);
        $client = new BeansClient($connInactive);
    }

    public function testInactiveConnectionException2() :void {
        $connInactive = $this->getConnection(false);

        $connActive = $this->getConnection(true);
        $client     = new BeansClient($connActive);

        $this->expectException(ClientException::class);
        $client->setConnection($connInactive);
    }
}
