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

    class Put extends CommandAbstract
    {
        public const MAX_PRIORITY                = 4294967295;
        public const MAX_SERIALOZED_PAYLOAD_SIZE = 65536;

        private $priority;
        private $delay;
        private $ttr;

        public
        function __construct($payload, int $priority, int $delay, int $ttr, ?Interfaces\Encoder $encoder = null) {
            if ($priority < 0 || $priority > self::MAX_PRIORITY) {
                throw new Exception\Command('Job priority must be between 0 and ' . self::MAX_PRIORITY);
            }
            if ($delay < 0) {
                throw new Exception\Command('Job delay must be a positive integer');
            }
            if ($ttr <= 0) {
                throw new Exception\Command('Job ttr must be greater than 0');
            }

            $this->commandName = Interfaces\Command::PUT;

            $this->priority = $priority;
            $this->delay    = $delay;
            $this->ttr      = $ttr;
            $this->payload  = $payload;

            $this->payloadEncoder = $encoder;
        }

        public
        function getCommandStr() :string {
            $mainCommand = $this->commandName . ' ' . $this->priority . ' ' . $this->delay . ' ' . $this->ttr . ' ';

            if ($this->payloadEncoder) {
                $serializedPayload = $this->payloadEncoder->encode($this->payload);
            }
            else if (!is_string($this->payload) || is_numeric($this->payload)) {
                throw new Exception\Command('Due to turned off payload encoder, job payload must be a string or number');
            }
            else {
                $serializedPayload = (string)$this->payload;
            }

            if (strlen($serializedPayload) > self::MAX_SERIALOZED_PAYLOAD_SIZE) {
                throw new Exception\Command('Job serialized payload size exceeded maximum: ' . self::MAX_SERIALOZED_PAYLOAD_SIZE);
            }

            return $mainCommand . strlen($serializedPayload) . BeansClient::CRLF . $serializedPayload;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) {
            if ($responseHeader[0] === Response::JOB_TOO_BIG) {
                throw new Exception\Command('Job\'s payload size exceeds max-job-size config');
            }
            else if ($responseHeader[0] !== Response::INSERTED) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }

            // ToDo: make handle of BURIED status

            return (int)$responseHeader[1];
        }
    }