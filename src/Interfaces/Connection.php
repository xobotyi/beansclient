<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Interfaces;


    interface Connection
    {
        public
        function __construct(string $host = 'localhost', int $port = -1, int $connectionTimeout = null, bool $persistent = false);

        public
        function disconnect() :void;

        public
        function getHost() :string;

        public
        function getPort() :int;

        public
        function isPersistent() :bool;

        public
        function isActive() :bool;

        public
        function write(string $str) :void;

        public
        function read(int $length) :string;

        public
        function readln(int $length = null) :string;
    }