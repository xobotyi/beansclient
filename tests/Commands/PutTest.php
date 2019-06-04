<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Command\Put;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Exception\JobException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class PutTest extends TestCase
{
    const HOST    = 'localhost';
    const PORT    = 11300;
    const TIMEOUT = 2;

    public
    function testPut() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls("INSERTED 1", "BURIED 2");

        $client = new BeansClient($conn);

        self::assertEquals(1, $client->put('test')->id);
        self::assertEquals('buried', $client->put('test')->state);
    }

    // test if server says that CRLF is missing

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

    // test if server says that job's payload is too big

    public
    function testPutException1() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("EXPECTED_CRLF"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test'));
    }

    // test if server is in draining mode

    public
    function testPutException10() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn, new JsonSerializer());

        $str   = '';
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        for ($i = 0; $i <= Put::MAX_SERIALIZED_PAYLOAD_SIZE + 1; $i++) {
            $str .= $chars[rand(0, 36)];
        }

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put($str));
    }

    // test if priority is less than 0

    public
    function testPutException11() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn, new JsonSerializer());

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put(''));
    }

    // test if delay id less than 0

    public
    function testPutException2() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("JOB_TOO_BIG"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test'));
    }

    // test if ttr is set to 0

    public
    function testPutException3() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("DRAINING"));
        $client = new BeansClient($conn);

        $this->expectException(JobException::class);
        self::assertEquals([], $client->put('test'));
    }

    // test if priority is too big

    public
    function testPutException4() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test', -1));
    }

    // test if payload is non-string value and serializer is not set

    public
    function testPutException5() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test', 0, -1));
    }

    // test if priority is not a number

    public
    function testPutException6() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test', 0, 0, 0));
    }

    // test if payload is too big;

    public
    function testPutException7() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('test', Put::MAX_PRIORITY + 1));
    }

    // test if job id somewhy is missing;

    public
    function testPutException8() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put([1, 2, 3]));
    }

    public
    function testPutException9() {
        $conn = $this->getConnection();
        $conn->method('readLine')
             ->will($this->returnValue("INSERTED"));
        $client = new BeansClient($conn);

        $this->expectException(CommandException::class);
        self::assertEquals([], $client->put('', ''));
    }
}
