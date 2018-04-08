<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;

    use PHPUnit\Framework\TestCase;
    use xobotyi\beansclient\Command\ListTubes;
    use xobotyi\beansclient\Command\Put;
    use xobotyi\beansclient\Command\Stats;
    use xobotyi\beansclient\Exception\Command;

    class CommandTest extends TestCase
    {
        public function testCommand() :void {
            $cmd = new Put('some payload', 0, 0, 1);

            self::assertEquals($cmd->getPayload(), 'some payload');
            self::assertEquals($cmd->hasPayload(), true);

            $cmd = new Stats();

            self::assertEquals($cmd->getCommandStr(), 'stats');
            self::assertEquals($cmd, 'stats');
        }

        public function testCommandException() {
            $cmd = new ListTubes();

            $this->expectException(Command::class);
            $cmd->parseResponse(['STUFF'], null);
        }
    }