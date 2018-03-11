<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;


    class Socket
    {
        public
        function fsockopen(string $hostName, int $port = -1, &$errNo = null, &$errStr = null, int $timeout = null) {
            return @\fsockopen($hostName, $port, $errNo, $errStr, $timeout);
        }

        public
        function pfsockopen(string $hostName, int $port = -1, &$errNo = null, &$errStr = null, int $timeout = null) {
            return @\pfsockopen($hostName, $port, $errNo, $errStr, $timeout);
        }

        public
        function fopen($filename, $mode) {
            return \fopen($filename, $mode);
        }

        public
        function fclose($stream) :bool {
            return \fclose($stream);
        }

        public
        function feof($stream) :bool {
            return \feof($stream);
        }

        public
        function fgets($stream, int $length = null) {
            if ($length) {
                return \fgets($stream, $length);
            }

            return \fgets($stream);
        }

        public
        function fread($stream, int $length) {
            return \fread($stream, $length);
        }

        public
        function fwrite($stream, string $text, int $length = null) {
            if ($length) {
                return \fwrite($stream, $text, $length);
            }

            return \fwrite($stream, $text);
        }

        public
        function setReadTimeout($stream, int $seconds, int $microseconds = 0) {
            return \stream_set_timeout($stream, $seconds, $microseconds);
        }
    }