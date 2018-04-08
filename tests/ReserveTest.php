<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;

    use PHPUnit\Framework\TestCase;
    use xobotyi\beansclient\Serializer\Json;
    use xobotyi\beansclient\Exception\Command;

    class ReserveTest extends TestCase
    {
        const HOST    = 'localhost';
        const PORT    = 11300;
        const TIMEOUT = 2;

        public function testReserve() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->withConsecutive()
                 ->willReturnOnConsecutiveCalls("TIMED_OUT", "RESERVED 1 9", "RESERVED 1 9");
            $conn->method('read')
                 ->withConsecutive([9], [2], [9], [2])
                 ->willReturnOnConsecutiveCalls("[1,2,3,4]", "\r\n", "[1,2,3,4]", "\r\n");

            $client = new BeansClient($conn);

            self::assertEquals(null, $client->reserve());
            self::assertEquals(['id' => 1, 'payload' => '[1,2,3,4]'], $client->reserve());

            $client = new BeansClient($conn, new Json());
            self::assertEquals(['id' => 1, 'payload' => [1, 2, 3, 4]], $client->reserve());
        }

        // test if response has wrong status name
        public function testReserveException1() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("SOME_STUFF"));

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->reserve();
        }

        // test if response has no data in
        public function testReserveException2() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("OK 0"));

            $conn->method('read')
                 ->withConsecutive([0], [2])
                 ->willReturnOnConsecutiveCalls("", "\r\n");

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->reserve();
        }

        // test if timeout < 0
        public function testReserveException3() :void {
            $conn = $this->getConnection();

            $conn->method('readln')
                 ->will($this->returnValue("TOUCHED"));

            $client = new BeansClient($conn);

            $this->expectException(Command::class);
            $client->reserve(-1);
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