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

    class Reserve extends CommandAbstract
    {
        private $timeout;

        public
        function __construct(?int $timeout = 0, ?Interfaces\Encoder $encoder = null) {
            if ($timeout < 0) {
                throw new Exception\Command('Timeout must be greater or equal than 0');
            }

            $this->commandName = Interfaces\Command::RESERVE;

            $this->timeout = $timeout;

            $this->setPayloadEncoder($encoder);
        }

        public
        function getCommandStr() :string {
            return $this->timeout === null ? $this->commandName : Interfaces\Command::RESERVE_WITH_TIMEOUT . ' ' . $this->timeout;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :?array {
            if ($responseHeader[0] === Response::TIMED_OUT) {
                return null;
            }
            else if ($responseHeader[0] !== Response::RESERVED) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            $res['id']      = (int)$responseHeader[1];
            $res['payload'] = $this->payloadEncoder ? $this->payloadEncoder->decode($responseStr) : $responseStr;

            return $res;
        }
    }