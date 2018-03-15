<?php
    /**
     * Created by PhpStorm.
     * User: xobotyi
     * Date: 15.03.2018
     * Time: 13:42
     */

    namespace xobotyi\beansclient;


    use PHPUnit\Framework\TestCase;

    class ConnectionExceptionTest extends TestCase
    {
        public
        function testConstructor() {
            $this->expectExceptionMessage("Connection error 123: test");
            throw new \xobotyi\beansclient\Exception\Connection(123, 'test');
        }
    }