<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;


    use xobotyi\beansclient\Command\CommandAbstract;
    use xobotyi\beansclient\Exception\Client;

    class BeansClient
    {
        /**
         * @var Interfaces\Connection
         */
        private $connection;

        const CRLF     = "\r\n";
        const CRLF_LEN = 2;

        public
        function __construct(Interfaces\Connection $connection) {
            $this->setConnection($connection);
        }

        public
        function setConnection(Interfaces\Connection $connection) :self {
            if (!$connection->isActive()) {
                throw new Client('Given connection is not active');
            }
            $this->connection = $connection;

            return $this;
        }

        public
        function getConnection() :Interfaces\Connection {

            return $this->connection;
        }

        public
        function dispatchCommand(CommandAbstract $cmd) {
            $request = $cmd->getCommandStr() . self::CRLF;

            $this->connection->write($request);

            $response = $this->connection->readln();

            list($status, $dataLength) = explode(' ', $response);
            var_dump($status, $dataLength);
        }
    }