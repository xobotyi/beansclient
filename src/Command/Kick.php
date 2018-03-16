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

    /**
     * Class Kick
     *
     * @package xobotyi\beansclient\Command
     */
    class Kick extends CommandAbstract
    {
        /**
         * @var int
         */
        private $count;

        /**
         * Kick constructor.
         *
         * @param int $count
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function __construct(int $count) {
            if ($count <= 0) {
                throw new Exception\Command('Kick count must be a positive integer');
            }

            $this->commandName = Interfaces\Command::KICK;

            $this->count = $count;
        }

        /**
         * @return string
         */
        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->count;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return int
         * @throws \xobotyi\beansclient\Exception\Command
         */
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