<?php
    /**
     * @Author : a.zinoviev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Exception;


    class Connection extends \Exception
    {
        public
        function __construct(int $errNo, string $errStr) {
            parent::__construct("Connection error {$errNo}: {$errStr}");
        }
    }