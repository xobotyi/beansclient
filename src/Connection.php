<?php
declare(strict_types=1);

namespace xobotyi\beansclient;

use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Exception\SocketFactoryException;
use xobotyi\beansclient\Interfaces\SocketInterface;
use xobotyi\beansclient\Socket\SocketFactory;

class Connection implements Interfaces\ConnectionInterface
{
    /**
     * @var null| SocketInterface
     */
    private $socket = null;

    /**
     * Connection constructor.
     *
     * @param string $host
     * @param int $port
     * @param null|int $connectionTimeout
     * @param SocketFactory|null $socketFactory
     *
     * @throws SocketException
     * @throws SocketFactoryException
     */
    public
    function __construct(?string $host = 'localhost', ?int $port = 11300, ?int $connectionTimeout = 2, SocketFactory $socketFactory = null)
    {
        $factory = $socketFactory ?? new SocketFactory($host ?? 'localhost', $port ?? 11300, $connectionTimeout ?? 2);

        $this->socket = $factory->createSocket();
    }

    /**
     * Disconnect the socket
     *
     * @return bool
     */
    public
    function disconnect(): bool
    {
        if (!$this->socket || !$this->socket->isConnected()) {
            return false;
        }

        $this->socket->disconnect();
        $this->socket = null;

        return true;
    }

    /**
     * Return the host connection been initialized with
     *
     * @return string
     */
    public
    function getHost(): ?string
    {
        return $this->socket ? $this->socket->getHost() : null;
    }

    /**
     * Return the port connection been initialized with
     *
     * @return int
     */
    public
    function getPort(): ?int
    {
        return $this->socket ? $this->socket->getPort() : null;
    }

    /**
     * Return the timeout connection been initialized with
     *
     * @return int
     */
    public
    function getConnectionTimeout(): ?int
    {
        return $this->socket ? $this->socket->getConnectionTimeout() : null;
    }

    /**
     * Return true if socket is opened
     *
     * @return bool
     */
    public
    function isActive(): bool
    {
        return $this->socket && $this->socket->isConnected();
    }

    /**
     * Reads up to $bytes bytes from the socket
     *
     * @param int $bytes Amount of bytes to read
     *
     * @return string
     */
    public
    function read(int $bytes): string
    {
        return $this->socket->read($bytes);
    }

    /**
     * Reads up to newline from socket
     *
     * @return string
     */
    public
    function readLine(): string
    {
        return $this->socket->readLine();
    }

    /**
     * Writes data to the socket
     *
     * @param string $data String to write into the socket
     *
     * @return int
     */
    public
    function write(string $data): int
    {
        return $this->socket->write($data);
    }
}
