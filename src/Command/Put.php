<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\BeansClient;
    use xobotyi\beansclient\Exception;
    use xobotyi\beansclient\Interfaces;
    use xobotyi\beansclient\Response;

    /**
     * Class Put
     *
     * @package xobotyi\beansclient\Command
     */
    class Put extends CommandAbstract
    {
        public const MAX_PRIORITY                = 4294967295;
        public const MAX_SERIALIZED_PAYLOAD_SIZE = 65536;

        /**
         * @var int|float
         */
        private $priority;
        /**
         * @var int
         */
        private $delay;
        /**
         * @var int
         */
        private $ttr;

        /**
         * Put constructor.
         *
         * @param                                                 $payload
         * @param                                                 $priority
         * @param int                                             $delay
         * @param int                                             $ttr
         * @param null|\xobotyi\beansclient\Interfaces\Serializer $serializer
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function __construct($payload, $priority, int $delay, int $ttr, ?Interfaces\Serializer $serializer = null) {
            if (!is_numeric($priority)) {
                throw new Exception\Command('Argument 2 passed to xobotyi\beansclient\BeansClient::put() must be a number, got ' . gettype($priority));
            }
            if ($priority < 0 || $priority > self::MAX_PRIORITY) {
                throw new Exception\Command('Job priority must be integer between 0 and ' . self::MAX_PRIORITY);
            }
            if ($delay < 0) {
                throw new Exception\Command('Job delay must be a positive integer');
            }
            if ($ttr <= 0) {
                throw new Exception\Command('Job ttr must be greater than 0');
            }

            $this->commandName = Interfaces\Command::PUT;

            $this->priority = floor($priority);
            $this->delay    = $delay;
            $this->ttr      = $ttr;
            $this->payload  = $payload;

            $this->setSerializer($serializer);
        }

        /**
         * @return string
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function getCommandStr() :string {
            $mainCommand = $this->commandName . ' ' . $this->priority . ' ' . $this->delay . ' ' . $this->ttr . ' ';

            if ($this->serializer) {
                $serializedPayload = $this->serializer->serialize($this->payload);
            }
            else if (!is_string($this->payload) && !is_numeric($this->payload)) {
                throw new Exception\Command('Due to turned off payload serializer, job payload must be a string or number');
            }
            else {
                $serializedPayload = (string)$this->payload;
            }

            if (strlen($serializedPayload) > self::MAX_SERIALIZED_PAYLOAD_SIZE) {
                throw new Exception\Command('Job serialized payload size exceeded maximum: ' . self::MAX_SERIALIZED_PAYLOAD_SIZE);
            }

            return $mainCommand . strlen($serializedPayload) . BeansClient::CRLF . $serializedPayload;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return array|null
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function parseResponse(array $responseHeader, ?string $responseStr) :?array {
            if ($responseHeader[0] === Response::JOB_TOO_BIG) {
                throw new Exception\Command('Job\'s payload size exceeds max-job-size config');
            }
            else if ($responseHeader[0] !== Response::INSERTED && $responseHeader[0] !== Response::BURIED) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!isset($responseHeader[1])) {
                throw new Exception\Command("Response is missing job id [" . implode('', $responseHeader) . "]");
            }

            return [
                'id'     => (int)$responseHeader[1],
                'status' => $responseHeader[0],
            ];
        }
    }