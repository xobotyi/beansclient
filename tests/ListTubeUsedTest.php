<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;

    use PHPUnit\Framework\TestCase;
    use xobotyi\beansclient\Command\Put;
    use xobotyi\beansclient\Serializer\Json;
    use xobotyi\beansclient\Exception\Client;
    use xobotyi\beansclient\Exception\Command;
    use xobotyi\beansclient\Exception\Job;

    class ListTubeUsedTest extends TestCase
    {
        const HOST    = 'localhost';
        const PORT    = 11300;
        const TIMEOUT = 2;

        public function testListTubeUsed() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("USING test1"));

            $client = new BeansClient($conn);

            self::assertEquals('test1', $client->listTubeUsed());
        }

        // test if tube name in response is missing
        public function testListTubeUsedException1() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("USING"));

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->listTubeUsed();
        }

        // test if response has wrong status name
        public function testListTubeUsedException2() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("SOME_STUFF"));

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->listTubeUsed();
        }

        // test if response has data in
        public function testListTubeUsedException3() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("OK 25"));

            $conn->method('read')
                 ->withConsecutive([25], [2])
                 ->willReturnOnConsecutiveCalls("---\r\n- default\r\n- test1", "\r\n");

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->listTubeUsed();
        }

        private function getConnection(bool $active = true) {
            $conn = $this->getMockBuilder('\xobotyi\beansclient\Connection')
                         ->disableOriginalConstructor()
                         ->getMock();

            $conn->expects($this->any())
                 ->method('isActive')
                 ->will($this->returnValue($active));

            return $conn;
        }
    }