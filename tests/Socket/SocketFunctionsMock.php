<?php

namespace xobotyi\beansclient\Socket
{
    function error_get_last($mockedResult = null) {
        static $res = true;

        return $res = $mockedResult === null ? $res : $mockedResult;
    }

    function gethostbynamel($hostname, $mockedResult = null) {
        static $res = ["127.0.0.1"];

        return $res = $mockedResult === null ? $res : $mockedResult;
    }

    function stream_context_create(array $options = null, array $params = null, $mockedResult = null) {
        return true;
    }

    function stream_socket_client($remote_socket, int &$errno = null, string &$errstr = null, int $timeout = null, int $flags = null, $context = null, $mockedResult = null) {
        static $res = true;

        $res = $mockedResult === null ? $res : $mockedResult;

        if (!$res) {
            $errno = 2;
            $errstr = 'Unable to establish connection';
        }

        return $res;
    }

    function stream_set_timeout($stream, int $seconds, int $microseconds = null, $mockedResult = null) {
        return true;
    }

    function fclose($stream) {
        return true;
    }

    function fread($handle, int $length, $mockedResult = null) {
        static $res = true;
        $res = $mockedResult === null ? $res : $mockedResult;

        if ($res === false) {
            return false;
        }
        else if ($res === '') {
            return "";
        }

        $str = '';
        $alphabet = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        for (; $length--;) {
            $str .= $alphabet[rand(0, 36)];
        }
        return $str;
    }

    function fgets($handle, int $length = null, $mockedResult = null) {
        static $res = true;
        $res = $mockedResult === null ? $res : $mockedResult;

        if ($res === false) {
            return false;
        }
        else if ($res === '') {
            return "";
        }

        return "123456789\r\n";
    }

    function fwrite($handle, $string, $length = null, $mockedResult = null) {
        static $res = 2;
        $res = $mockedResult === null ? $res : $mockedResult;
        return $res;
    }
}