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

    class ListTubesTest extends TestCase
    {
        const HOST    = 'localhost';
        const PORT    = 11300;
        const TIMEOUT = 2;

        public
        function testListTubes() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("OK 25"));

            $conn->method('read')
                 ->withConsecutive([25], [2])
                 ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

            $client = new BeansClient($conn);

            self::assertEquals(['default', 'test1'], $client->listTubes());
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