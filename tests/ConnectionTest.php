<?php

namespace xobotyi\beansclient;

include_once __DIR__ . "/Socket/SocketFunctionsMock.php";

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\SocketException;

class ConnectionTest extends TestCase
{
    public
    function testDefaultParameters() {
        \xobotyi\beansclient\Socket\gethostbynamel(null, ["127.0.0.1"]);
        \xobotyi\beansclient\Socket\stream_socket_client("", $err, $errstr, 0, 0, null, true);

        $conn = new Connection();

        $this->assertTrue($conn->isActive());
        $this->assertFalse($conn->isPersistent());

        $this->assertSame('localhost', $conn->getHost());
        $this->assertSame(11300, $conn->getPort());
        $this->assertSame(1, $conn->getTimeout());

        $this->assertTrue($conn->disconnect());
        $this->assertFalse($conn->disconnect());
    }

    public
    function testReads() {
        \xobotyi\beansclient\Socket\gethostbynamel(null, ["127.0.0.1"]);
        \xobotyi\beansclient\Socket\stream_socket_client("", $err, $errstr, 0, 0, null, true);
        \xobotyi\beansclient\Socket\fread("", 23, true);
        \xobotyi\beansclient\Socket\fgets("", 23, true);

        $conn = new Connection();

        $this->assertEquals(64, mb_strlen($conn->read(64), "8bit"));
        $this->assertEquals(9, mb_strlen($conn->readLine(), "8bit"));
    }

    public
    function testWrite() {
        \xobotyi\beansclient\Socket\gethostbynamel(null, ["127.0.0.1"]);
        \xobotyi\beansclient\Socket\stream_socket_client("", $err, $errstr, 0, 0, null, true);
        \xobotyi\beansclient\Socket\fwrite("", "", 23, 9);

        $conn = new Connection();

        $err = null;
        try {
            $conn->write("123456789");
        }
        catch (SocketException $ex) {
            $err = $ex;
        }

        $this->assertNull($err);
    }
}
