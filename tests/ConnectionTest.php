<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

function fsockopen($hostname, $port = null, &$errno = null, &$errstr = null, $timeout = null, $mockResponse = null) {
    static $res = true;

    $res = $mockResponse === null ? $res : $mockResponse;

    if (!$res) {
        $errno  = 0;
        $errstr = 'Unable to establish connection';
    }

    return $res;
}

function pfsockopen($hostname, $port = null, &$errno = null, &$errstr = null, $timeout = null, $mockResponse = null) {
    static $res = true;

    $res = $mockResponse === null ? $res : $mockResponse;

    if (!$res) {
        $errno  = 0;
        $errstr = 'Unable to establish connection';
    }

    return $res;
}

function fclose($handle, $mockResponse = null) {
    static $res = true;

    $res = $mockResponse === null ? $res : $mockResponse;

    return $res;
}

function feof($handle = null, $stateOverride = null) {
    static $state = true;

    $state = $stateOverride === null ? $state : !$stateOverride;

    return $state = !$state;
}

function fgets($handle, $length = null) {
    if ($length === null) {
        return '123456789';
    }

    $str   = '';
    $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, 36)];
    }

    return $str;
}

function fread($handle, $length, $mockResponse = null) {
    static $res = true;

    $res = $mockResponse === null ? $res : $mockResponse;


    if ($res === false) {
        return false;
    }

    $str   = '';
    $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, 36)];
    }

    return $str;
}

function fwrite($handle, $string, $length = null, $mockResponse = null) {
    static $res = 2;

    $res = $mockResponse === null ? $res : $mockResponse;

    return $res;
}

function stream_set_timeout($stream, $seconds, $microseconds = null) { return true; }

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnection() {
        $conn = new Connection();

        self::assertTrue($conn->isActive());
        self::assertFalse($conn->isPersistent());
        self::assertTrue($conn->disconnect());

        $conn = new Connection('localhost', 11300, 2, true);

        self::assertTrue($conn->isActive());
        self::assertEquals('localhost', $conn->getHost());
        self::assertEquals(11300, $conn->getPort());
        self::assertEquals(2, $conn->getTimeout());
        self::assertTrue($conn->isPersistent());
        self::assertTrue($conn->disconnect());
        self::assertFalse($conn->disconnect());

        $conn = new Connection('localhost', 11300, 2);
        $conn->write('stuff');
        self::assertEquals(15, strlen($conn->read(15)));
        self::assertEquals(2, $conn->fwrite(null, 'ab', 2));

        feof(null, true);
        self::assertEquals('123456789', $conn->readLine());
        feof(null, true);
        self::assertEquals(15, strlen($conn->readLine(15)));
    }

    public function testConnectionException() {
        $conn = new Connection('localhost', 11300, 2);
        self::assertTrue($conn->disconnect());

        $this->expectException(Exception\ConnectionException::class);
        $conn->write(123);
    }

    public function testConnectionException1() {
        $conn = new Connection('localhost', 11300, 2);
        self::assertTrue($conn->disconnect());

        $this->expectException(Exception\ConnectionException::class);
        $conn->readLine();
    }

    public function testConnectionException2() {
        $conn = new Connection('localhost', 11300, 2);
        self::assertTrue($conn->disconnect());

        $this->expectException(Exception\ConnectionException::class);
        $conn->read(15);
    }

    public function testConnectionException3() {
        $conn = new Connection('localhost', 11300, 2);

        $this->expectException(Exception\ConnectionException::class);

        fclose(null, false);
        self::assertTrue($conn->disconnect());
    }

    public function testConnectionException4() {
        $conn = new Connection('localhost', 11300, 2);

        fclose(null, false);

        $this->expectException(Exception\ConnectionException::class);
        unset($conn);
    }

    public function testConnectionException5() {
        fclose(null, true);
        fsockopen(null, null, $errno, $errstr, null, false);

        $this->expectException(Exception\ConnectionException::class);
        $conn = new Connection('localhost', 11300, 2);
    }

    public function testConnectionException6() {
        fsockopen(null, null, $errno, $errstr, null, true);

        $conn = new Connection('localhost', 11300, 2);

        feof(null, false);

        $this->expectException(Exception\SocketException::class);
        $conn->readLine();
    }

    public function testConnectionException7() {
        feof(null, true);
        fwrite(null, null, null, 0);

        $conn = new Connection('localhost', 11300, 2);

        $this->expectException(Exception\SocketException::class);
        $conn->write(123);
    }

    public function testConnectionException8() {
        fwrite(null, null, null, 2);
        fread(null, null, false);

        $conn = new Connection('localhost', 11300, 2);

        $this->expectException(Exception\SocketException::class);
        $conn->read(123);
    }
}
