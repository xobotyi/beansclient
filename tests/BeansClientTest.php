<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;

    use PHPUnit\Framework\TestCase;
    use xobotyi\beansclient\Command\Put;
    use xobotyi\beansclient\Encoder\Json;
    use xobotyi\beansclient\Exception\Client;
    use xobotyi\beansclient\Exception\Command;
    use xobotyi\beansclient\Exception\Server;

    class BeansClientTest extends TestCase
    {
        const HOST    = 'localhost';
        const PORT    = 11300;
        const TIMEOUT = 2;

        public
        function testInactiveConnectionException1() :void {
            $connInactive = $this->getConnection(false);

            $this->expectException(Client::class);
            $client = new BeansClient($connInactive);
        }

        public
        function testInactiveConnectionException2() :void {
            $connInactive = $this->getConnection(false);

            $connActive = $this->getConnection(true);
            $client     = new BeansClient($connActive);

            $this->expectException(Client::class);
            $client->setConnection($connInactive);
        }

        public
        function testActiveConnectionException() :void {
            $connActive = $this->getConnection(true);
            $client     = new BeansClient($connActive);

            self::assertEquals($connActive, $client->getConnection());
        }

        public
        function testGetters() :void {
            $conn = $this->getConnection();

            $client = new BeansClient($conn);

            self::assertEquals($conn, $client->getConnection());
        }

        public
        function testPut() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED 1 4"));

            $client = new BeansClient($conn);

            self::assertEquals(['id' => 1, 'status' => 'INSERTED'], $client->put('test'));

            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("BURIED 1 4"));
            $client->setConnection($conn);
            self::assertEquals(['id' => 1, 'status' => 'BURIED'], $client->put('test'));

            $this->expectException(Command::class);
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("EXPECTED_CRLF"));
            $client->setConnection($conn);
            self::assertEquals([], $client->put('test'));

            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("JOB_TOO_BIG"));
            $client->setConnection($conn);
            self::assertEquals([], $client->put('test'));

            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("DRAINING"));
            $client->setConnection($conn);
            self::assertEquals([], $client->put('test'));
        }

        public
        function testPutException1() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("EXPECTED_CRLF"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test'));
        }

        public
        function testPutException2() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("JOB_TOO_BIG"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test'));
        }

        public
        function testPutException3() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("DRAINING"));
            $client = new BeansClient($conn);

            $this->expectException(Server::class);
            self::assertEquals([], $client->put('test'));
        }

        public
        function testPutException4() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test', -1));
        }

        public
        function testPutException5() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test', 0, -1));
        }

        public
        function testPutException6() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test', 0, -1, 0));
        }

        public
        function testPutException7() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            self::assertEquals([], $client->put('test', Put::MAX_PRIORITY + 1));
        }

        public
        function testPutException8() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn, new Json());

            $this->expectException(Command::class);
            self::assertEquals([], $client->put([1, 2, 3]));
        }

        public
        function testPutException9() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn, new Json());

            $this->expectException(Command::class);
            self::assertEquals([], $client->put(''));
        }

        public
        function testPutException10() {
            $conn = $this->getConnection();
            $conn->method('readln')
                 ->will($this->returnValue("INSERTED"));
            $client = new BeansClient($conn, new Json());

            $str   = '';
            $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
            for ($i = 0; $i <= Put::MAX_SERIALIZED_PAYLOAD_SIZE + 1; $i++) {
                $str .= $chars[rand(0, 36)];
            }

            $this->expectException(Command::class);
            self::assertEquals([], $client->put($str));
        }

        private
        function getConnection(bool $active = true) {
            $conn = $this->getMockBuilder('\xobotyi\beansclient\Connection')
                         ->disableOriginalConstructor()
                         ->getMock();

            $conn->expects($this->any())
                 ->method('isActive')
                 ->will($this->returnValue($active));

            return $conn;
        }
    }