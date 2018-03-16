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

    abstract
    class CommandAbstract implements Interfaces\Command
    {
        protected $payload;

        /**
         * @var \xobotyi\beansclient\Interfaces\Serializer
         */
        protected $serializer;

        protected $commandName;

        public
        function __toString() :string {
            return $this->getCommandStr();
        }

        public
        function hasPayload() :bool {
            return (bool)$this->payload;
        }

        public
        function getPayload() {
            return $this->payload;
        }

        public
        function setSerializer(?Interfaces\Serializer $serialize) :self {
            $this->serializer = $serialize;

            return $this;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) {
            if ($responseHeader[0] !== Response::OK) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            return Response::YamlParse($responseStr);
        }
    }