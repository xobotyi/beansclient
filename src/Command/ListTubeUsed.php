<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\Exception\Command;
    use xobotyi\beansclient\Interfaces;
    use xobotyi\beansclient\Response;

    /**
     * Class ListTubeUsed
     *
     * @package xobotyi\beansclient\Command
     */
    class ListTubeUsed extends CommandAbstract
    {
        /**
         * ListTubeUsed constructor.
         */
        public
        function __construct() {
            $this->commandName = Interfaces\Command::LIST_TUBE_USED;
        }

        /**
         * @return string
         */
        public
        function getCommandStr() :string {
            return $this->commandName;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return string
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function parseResponse(array $responseHeader, ?string $responseStr) :string {
            if ($responseStr) {
                throw new Command("Unexpected response data passed");
            }
            else if ($responseHeader[0] === Response::USING) {
                if (!isset($responseHeader[1])) {
                    throw new Command("Response is missing tube name [" . implode('', $responseHeader) . "]");
                }

                return $responseHeader[1];
            }

            throw new Command("Got unexpected status code [${responseHeader[0]}]");
        }
    }