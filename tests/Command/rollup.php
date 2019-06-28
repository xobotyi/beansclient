<?php

use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\BeansClient;
use xobotyi\beansclient\Connection;

function getBeansclientMock(TestCase $test, bool $activeConnection = true) {
    $conn = $test->getMockBuilder(Connection::class)
                 ->disableOriginalConstructor()
                 ->getMock();

    if (!$activeConnection) {
        $conn->method('isActive')
             ->withConsecutive()
             ->willReturnOnConsecutiveCalls(true, $activeConnection);
    }
    else {
        $conn->method('isActive')
             ->willReturn(true);
    }

    return $test->getMockBuilder(BeansClient::class)
                ->setConstructorArgs([$conn]);
}

function getConnectionMock(TestCase $test, bool $active = true) {
    $conn = $test->getMockBuilder(Connection::class)
                 ->disableOriginalConstructor()
                 ->getMock();

    $conn->expects($test->any())
         ->method('isActive')
         ->will($test->returnValue($active));

    return $conn;
}