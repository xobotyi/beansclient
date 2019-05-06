<?php

namespace xobotyi\beansclient\Socket;

include __DIR__ . "/SocketFunctionsMock.php";

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\SocketException;

class StreamSocketTest extends TestCase
{
    public
    function testUnreachableHostException() {
        gethostbynamel(null, false);

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Host 'localhost' not exists or unreachable");

        $sock = new StreamSocket();
    }

    public
    function testConnectionErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, false);

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Unable to establish connection");
        $this->expectExceptionCode(2);

        $sock = new StreamSocket();
    }

    public
    function testInstance() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);

        $sock = new StreamSocket();

        $this->assertFalse($sock->isClosed());
        $this->assertFalse($sock->isPersistent());
        $this->assertEquals('localhost', $sock->getHost());
        $this->assertEquals(11300, $sock->getPort());
        $this->assertEquals(StreamSocket::CONNECTION_TIMEOUT, $sock->getTimeout());
    }

    public
    function testReadClosedException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);

        $sock = new StreamSocket();

        $this->assertEquals($sock->close(), $sock);

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Socked is closed");

        $sock->read(12);
    }

    public
    function testReadErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fread("", 23, false);
        error_get_last([
                           'message' => "Some silly error",
                           'type'    => 2,
                       ]);

        $sock = new StreamSocket();


        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Some silly error");
        $this->expectExceptionCode(2);

        $sock->read(64);
    }

    public
    function testReadUnknownErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fread("", 23, false);
        error_get_last(false);

        $sock = new StreamSocket();

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Unknown error");
        $this->expectExceptionCode(0);

        $sock->read(64);
    }

    public
    function testReadFailedAfterRetriesException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fread("", 23, "");
        error_get_last(false);

        $sock = new StreamSocket();

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Failed to read 64 bytes from socket after 5 retries, got only 0 bytes (localhost:11300)");

        $sock->read(64);
    }

    public
    function testRead() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fread("", 23, true);

        $sock = new StreamSocket();

        $this->assertEquals(64, mb_strlen($sock->read(64), "8bit"));
    }

    public
    function testReadLineClosedException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);

        $sock = new StreamSocket();

        $this->assertEquals($sock->close(), $sock);

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Socked is closed");

        $sock->readLine();
    }

    public
    function testReadLineErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fgets("", 23, false);
        error_get_last([
                           'message' => "Some silly error",
                           'type'    => 2,
                       ]);

        $sock = new StreamSocket();


        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Some silly error");
        $this->expectExceptionCode(2);

        $sock->readLine();
    }

    public
    function testReadLineUnknownErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fgets("", 23, false);
        error_get_last(false);

        $sock = new StreamSocket();

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Unknown error");
        $this->expectExceptionCode(0);

        $sock->readLine();
    }

    public
    function testReadLine() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fgets("", 23, true);

        $sock = new StreamSocket();

        $this->assertEquals(9, mb_strlen($sock->readLine(), "8bit"));
    }

    public
    function testWriteClosedException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);

        $sock = new StreamSocket();

        $this->assertEquals($sock->close(), $sock);

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Socked is closed");

        $sock->write("123123");
    }

    public
    function testWriteErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fwrite("", "", 23, false);
        error_get_last([
                           'message' => "Some silly error",
                           'type'    => 2,
                       ]);

        $sock = new StreamSocket();


        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Some silly error");
        $this->expectExceptionCode(2);

        $sock->write("123123");
    }

    public
    function testWriteUnknownErrorException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fwrite("", "", 23, false);
        error_get_last(false);

        $sock = new StreamSocket();

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Unknown error");
        $this->expectExceptionCode(0);

        $sock->write("123123");
    }

    public
    function testWriteFailedAfterRetriesException() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fwrite("", "", 23, 0);
        error_get_last(false);

        $sock = new StreamSocket();

        $this->expectException(SocketException::class);
        $this->expectExceptionMessage("Failed to write data to socket after 5 retries (localhost:11300)");

        $sock->write("123123");
    }

    public
    function testWrite() {
        gethostbynamel(null, ["127.0.0.1"]);
        stream_socket_client("", $err, $errstr, 0, 0, null, true);
        fwrite("", "", 23, 9);
        error_get_last(false);

        $sock = new StreamSocket();

        $err = null;
        try {
            $sock->write("123456789");
        }
        catch (SocketException $ex) {
            $err = $ex;
        }

        $this->assertNull($err);
    }
}