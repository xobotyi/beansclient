<?php
    /**
     * Created by PhpStorm.
     * User: xobotyi
     * Date: 15.03.2018
     * Time: 16:09
     */

    namespace xobotyi\beansclient;

    function fsockopen(string $hostname, int $port = null, int &$errno = null, string &$errstr = null, float $timeout = null) {
        return true;
    }

    function pfsockopen(string $hostname, int $port = null, int &$errno = null, string &$errstr = null, float $timeout = null) {
        return true;
    }

    function fclose($handle) {
        return true;
    }

    function feof($handle = null, bool $stateOverride = null) {
        static $state = true;

        $state = $stateOverride === null ? $state : !$stateOverride;

        return $state = !$state;
    }

    function fgets($handle, int $length = null) {
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

    function fread($handle, int $length) {
        $str   = '';
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, 36)];
        }

        return $str;
    }

    function fwrite($handle, string $string, int $length = null) { return strlen($string); }

    function stream_set_timeout($stream, int $seconds, int $microseconds = null) { }

    use PHPUnit\Framework\TestCase;

    class ConnectionTest extends TestCase
    {
        public
        function testConnection() {
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

            $conn->write('stuff');
            self::assertEquals(15, strlen($conn->read(15)));
            self::assertEquals(2, $conn->fwrite(null, 'ab', 2));

            feof(null, true);
            self::assertEquals('123456789', $conn->readln());
            feof(null, true);
            self::assertEquals(15, strlen($conn->readln(15)));
        }
    }