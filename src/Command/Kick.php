<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\Exception;
    use xobotyi\beansclient\Interfaces;
    use xobotyi\beansclient\Response;

    class Kick extends CommandAbstract
    {
        private $count;

        public
        function __construct(int $count) {
            if ($count <= 0) {
                throw new Exception\Command('Kick count must be a positive integer');
            }

            $this->commandName = Interfaces\Command::KICK;

            $this->count = $count;
        }

        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->count;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :int {
            if ($responseStr) {
                throw new Exception\Command("Unexpected response data passed");
            }
            else if ($responseHeader[0] === Response::KICKED) {
                return (int)$responseHeader[1];
            }

            throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
        }
    }