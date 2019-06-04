<?php


namespace xobotyi\beansclient;

use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public
    function testClientException1() {
        $this->expectException(Exception\ClientException::class);
        $this->expectExceptionMessage("test");
        throw new Exception\ClientException('test');
    }

    public
    function testCommandException1() {
        $this->expectException(Exception\CommandException::class);
        $this->expectExceptionMessage("test");
        throw new Exception\CommandException('test');
    }

    public
    function testJobException1() {
        $this->expectException(Exception\JobException::class);
        $this->expectExceptionMessage("test");
        throw new Exception\JobException('test');
    }

    public
    function testServerException1() {
        $this->expectException(Exception\ServerException::class);
        $this->expectExceptionMessage("test");
        throw new Exception\ServerException('test');
    }

    public
    function testSocketException1() {
        $this->expectException(Exception\SocketException::class);
        $this->expectExceptionMessage("test");
        throw new Exception\SocketException('test');
    }
}
