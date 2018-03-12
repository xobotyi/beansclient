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

    class Stats extends CommandAbstract
    {
        public
        function __construct() {
            $this->commandName = Interfaces\Command::STATS;
        }

        public
        function getCommandStr() :string {
            return $this->commandName;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) {
            if ($responseHeader[0] !== Response::OK) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            return Response::YamlParse($responseStr, true);
        }
    }