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
     * Class CommandAbstract
     *
     * @package xobotyi\beansclient\Command
     */
    abstract
    class CommandAbstract implements Interfaces\Command
    {
        /**
         * @var array|string|int|float
         */
        protected $payload;

        /**
         * @var \xobotyi\beansclient\Interfaces\Serializer
         */
        protected $serializer;

        /**
         * @var string
         */
        protected $commandName;

        /**
         * @return string
         */
        public
        function __toString() :string {
            return $this->getCommandStr();
        }

        /**
         * @return bool
         */
        public
        function hasPayload() :bool {
            return (bool)$this->payload;
        }

        /**
         * @return mixed
         */
        public
        function getPayload() {
            return $this->payload;
        }

        /**
         * @param null|\xobotyi\beansclient\Interfaces\Serializer $serialize
         *
         * @return \xobotyi\beansclient\Command\CommandAbstract
         */
        public
        function setSerializer(?Interfaces\Serializer $serialize) :self {
            $this->serializer = $serialize;

            return $this;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return array|mixed|null
         * @throws \Exception
         * @throws \xobotyi\beansclient\Exception\Command
         */
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