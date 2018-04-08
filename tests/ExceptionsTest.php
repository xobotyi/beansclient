<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;

    use PHPUnit\Framework\TestCase;

    class ExceptionsTest extends TestCase
    {
        public function testConnectionException1() {
            $this->expectException(Exception\Connection::class);
            $this->expectExceptionMessage("Connection error 123: test");
            throw new Exception\Connection(123, 'test');
        }

        public function testClientException1() {
            $this->expectException(Exception\Client::class);
            $this->expectExceptionMessage("test");
            throw new Exception\Client('test');
        }

        public function testServerException1() {
            $this->expectException(Exception\Job::class);
            $this->expectExceptionMessage("test");
            throw new Exception\Job('test');
        }

        public function testSocketException1() {
            $this->expectException(Exception\Socket::class);
            $this->expectExceptionMessage("test");
            throw new Exception\Socket('test');
        }

        public function testCommandException1() {
            $this->expectException(Exception\Command::class);
            $this->expectExceptionMessage("test");
            throw new Exception\Command('test');
        }
    }