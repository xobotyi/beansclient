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
            $conn    = $this->getConnection();
            $encoder = new Json();

            $client = new BeansClient($conn, $encoder);

            self::assertEquals($conn, $client->getConnection());
            self::assertEquals($encoder, $client->getEncoder());
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