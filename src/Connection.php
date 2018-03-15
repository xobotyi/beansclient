<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient;


    class Connection extends SocketFunctions implements Interfaces\Connection
    {
        const SOCK_CONNECTION_TIMEOUT = 1;
        const SOCK_READ_TIMEOUT       = 1;
        const SOCK_WRITE_RETRIES      = 8;

        private $host;
        private $port;
        private $timeout;
        private $persistent;

        private $socket;

        /**
         * Connection constructor.
         *
         * @param string $host
         * @param int    $port
         * @param int    $connectionTimeout
         * @param bool   $persistent
         *
         * @throws Exception\Connection
         */
        public
        function __construct(string $host = 'localhost', int $port = -1, int $connectionTimeout = null, bool $persistent = false) {
            $this->host       = $host;
            $this->port       = $port;
            $this->timeout    = $connectionTimeout === null ? self::SOCK_CONNECTION_TIMEOUT : $connectionTimeout;
            $this->persistent = $persistent;

            $this->socket = $persistent
                ? $this->pfsockopen($this->host, $this->port, $errNo, $errStr, $this->timeout)
                : $this->fsockopen($this->host, $this->port, $errNo, $errStr, $this->timeout);

            if (!$this->socket) {
                throw new Exception\Connection($errNo, $errStr . " (while connecting to {$this->host}:{$this->port})");
            }

            $this->setReadTimeout($this->socket, self::SOCK_READ_TIMEOUT);
        }

        public
        function __destruct() {
            if (!$this->persistent) {
                if ($this->fclose($this->socket)) {
                    $this->socket = null;
                }
                else {
                    throw new Exception\Connection(0, "Unable to close connection");
                }
            }
        }

        /**
         *  Disconnect the socket
         */
        public
        function disconnect() :bool {
            if (!$this->socket) {
                return false;
            }

            if ($this->fclose($this->socket)) {
                $this->socket = null;
            }
            else {
                throw new Exception\Connection(0, "Unable to close connection");
            }

            return !$this->isActive();
        }

        /**
         * @return string
         */
        public
        function getHost() :string {
            return $this->host;
        }

        /**
         * @return int
         */
        public
        function getPort() :int {
            return $this->port;
        }

        /**
         * @return int
         */
        public
        function getTimeout() :int {
            return $this->timeout;
        }

        /**
         * @return bool
         */
        public
        function isPersistent() :bool {
            return $this->persistent;
        }

        /**
         * @return bool
         */
        public
        function isActive() :bool {
            return !!$this->socket;
        }

        /**
         * @param string $str
         *
         * Writes data to the socket
         *
         * @throws \xobotyi\beansclient\Exception\Connection
         * @throws \xobotyi\beansclient\Exception\Socket
         */
        public
        function write(string $str) :void {
            if (!$this->socket) {
                throw new Exception\Connection(0, "Unable to write into closed connection");
            }

            for ($attempt = $written = $iterWritten = 0; $written < strlen($str); $written += $iterWritten) {
                $iterWritten = $this->fwrite($this->socket, substr($str, $written));

                if (++$attempt === self::SOCK_WRITE_RETRIES) {
                    throw new Exception\Socket(sprintf("Failed to write data to socket after %u retries (%u:%u)", self::SOCK_WRITE_RETRIES, $this->host, $this->port));
                }
            }
        }

        /**
         * Reads up to $length bytes from socket
         *
         * @param int|null $length
         *
         * @return string
         * @throws \xobotyi\beansclient\Exception\Connection
         * @throws \xobotyi\beansclient\Exception\Socket
         */
        public
        function read(int $length) :string {
            if (!$this->socket) {
                throw new Exception\Connection(0, "Unable to read from closed connection");
            }

            $str  = '';
            $read = 0;

            while ($read < $length && !$this->feof($this->socket)) {
                $data = $this->fread($this->socket, $length);

                if ($data === false) {
                    throw new Exception\Socket(sprintf("Failed to read data from socket ({$this->host}:{$this->port})"));
                }

                $read += strlen($data);
                $str  .= $data;
            }

            return $str;
        }

        /**
         * Reads up to newline or $length-1 bytes from socket
         *
         * @param int|null $length
         *
         * @return string
         * @throws \xobotyi\beansclient\Exception\Connection
         * @throws \xobotyi\beansclient\Exception\Socket
         */
        public
        function readln(int $length = null) :string {
            if (!$this->socket) {
                throw new Exception\Connection(0, "Unable to read from closed connection");
            }

            $str = false;

            while ($str === false) {
                $str = isset($length)
                    ? $this->fgets($this->socket, $length)
                    : $this->fgets($this->socket);

                if ($this->feof($this->socket)) {
                    throw new Exception\Socket(sprintf("Socket closed by remote ({$this->host}:{$this->port})"));
                }
            }

            return rtrim($str);
        }
    }