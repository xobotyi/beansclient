<?php
declare(strict_types=1);

namespace xobotyi\beansclient;

use xobotyi\beansclient\Socket\StreamSocket;

class Connection implements Interfaces\ConnectionInterface
{
    /**
     * @var null| \xobotyi\beansclient\Interfaces\SocketInterface
     */
    private $socket = null;

    /**
     * Connection constructor.
     *
     * @param string   $host
     * @param int      $port
     * @param null|int $connectionTimeout
     * @param bool     $persistent
     *
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function __construct(string $host = 'localhost', int $port = 11300, ?int $connectionTimeout = null, bool $persistent = false) {
        $this->socket = new StreamSocket($host, $port, $connectionTimeout, $persistent);
    }

    /**
     * Disconnect the socket
     *
     * @return bool
     */
    public
    function disconnect(): bool {
        if (!$this->socket || $this->socket->isClosed()) {
            return false;
        }

        $this->socket->close();
        $this->socket = null;

        return true;
    }

    /**
     * Return the host connection been initialized with
     *
     * @return string
     */
    public
    function getHost(): ?string {
        return $this->socket ? $this->socket->getHost() : null;
    }

    /**
     * Return the port connection been initialized with
     *
     * @return int
     */
    public
    function getPort(): ?int {
        return $this->socket ? $this->socket->getPort() : null;
    }

    /**
     * Return the timeout connection been initialized with
     *
     * @return int
     */
    public
    function getTimeout(): ?int {
        return $this->socket ? $this->socket->getTimeout() : null;
    }

    /**
     * Return true if socket is opened
     *
     * @return bool
     */
    public
    function isActive(): bool {
        return $this->socket && !$this->socket->isClosed();
    }

    /**
     * Return true if socket is persistent
     *
     * @return bool
     */
    public
    function isPersistent(): bool {
        return $this->socket && $this->socket->isPersistent();
    }

    /**
     * Reads up to $bytes bytes from the socket
     *
     * @param int      $bytes   Amount of bytes to read
     * @param int|null $timeout Amount of seconds to wait the response
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function read(int $bytes, int $timeout = null): string {
        return $this->socket->read($bytes, $timeout);
    }

    /**
     * Reads up to newline from socket
     *
     * @param int|null $timeout Amount of seconds to wait the response
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function readLine(int $timeout = null): string {
        return $this->socket->readLine($timeout);
    }

    /**
     * Writes data to the socket
     *
     * @param string $data String to write into the socket
     *
     * @return int
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function write(string $data): int {
        return $this->socket->write($data);
    }
}
