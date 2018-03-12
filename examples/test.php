<?php
    /**
     * @Author : a.zinoviev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    include_once __DIR__ . '/../vendor/autoload.php';

    use xobotyi\beansclient;

    $connection = new beansclient\Connection('127.0.0.1', 11300);
    $client     = new beansclient\BeansClient($connection);