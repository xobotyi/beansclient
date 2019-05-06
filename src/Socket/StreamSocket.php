<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;

/**
 * Class StreamSocket
 * @package xobotyi\beansclient\Socket
 */
class StreamSocket extends SocketBase
{
    /**
     * @param string   $host
     * @param int      $port
     * @param int|null $timeout
     *
     * @param bool     $persistent
     *
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function __construct(string $host = 'localhost', int $port = 11300, ?int $timeout = null, bool $persistent = false) {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout === null ? static::CONNECTION_TIMEOUT : $timeout;
        $this->persistent = $persistent;

        $ip = gethostbynamel($this->host);
        if (empty($ip)) {
            throw new SocketException("Host '{$this->host}' not exists or unreachable");
        }

        $uri = "tcp://{$ip[0]}:{$this->port}";
        $flags = $this->persistent ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT;
        $context = stream_context_create();

        $this->socket = @stream_socket_client($uri, $errno, $msg, $this->timeout, $flags, $context);

        if (!$this->socket || $errno || $msg) {
            throw new SocketException($msg, $errno);
        }

        stream_set_timeout($this->socket, static::READ_TIMEOUT);
    }
}