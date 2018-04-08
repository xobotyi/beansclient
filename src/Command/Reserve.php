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
     * Class Reserve
     *
     * @package xobotyi\beansclient\Command
     */
    class Reserve extends CommandAbstract
    {
        /**
         * @var int|null
         */
        private $timeout;

        /**
         * Reserve constructor.
         *
         * @param int|null                                        $timeout
         * @param null|\xobotyi\beansclient\Interfaces\Serializer $serializer
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function __construct(?int $timeout = 0, ?Interfaces\Serializer $serializer = null) {
            if ($timeout < 0) {
                throw new Exception\Command('Timeout must be greater or equal than 0');
            }

            $this->commandName = Interfaces\Command::RESERVE;

            $this->timeout = $timeout;

            $this->setSerializer($serializer);
        }

        /**
         * @return string
         */
        public function getCommandStr() :string {
            return $this->timeout === null ? $this->commandName : Interfaces\Command::RESERVE_WITH_TIMEOUT . ' ' . $this->timeout;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return array|null
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function parseResponse(array $responseHeader, ?string $responseStr) :?array {
            if ($responseHeader[0] === Response::TIMED_OUT) {
                return null;
            }
            else if ($responseHeader[0] !== Response::RESERVED) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            return [
                'id'      => (int)$responseHeader[1],
                'payload' => $this->serializer ? $this->serializer->unserialize($responseStr) : $responseStr,
            ];
        }
    }