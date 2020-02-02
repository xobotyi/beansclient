<?php


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;

class SocketSocket
{
    protected $host;
    protected $port;
    protected $connectionTimeout;
    protected $persistent;

    public $socket;

    private const SOCKET_PROTO = SOL_TCP;

    /**
     * SocketSocket constructor.
     *
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     *
     * @param bool $persistent
     * @throws SocketException
     */
    public function __construct(string $host, int $port, int $connectionTimeout, bool $persistent)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
        $this->persistent = $persistent;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, self::SOCKET_PROTO);

        if (!$this->socket) {
            $this->throwLastError();
        }

        $timeout = [
            'sec' => $connectionTimeout,
            'usec' => 0
        ];

        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);

        if (!@socket_connect($this->socket, $host, $port)) {
            $this->throwLastError(true);
        }

        $ip = gethostbynamel($this->host);
        if (empty($ip)) {
            throw new SocketException(sprintf('Host `%s` not exists or unreachable', $this->host));
        }
    }

    /**
     * Throws last socket error if it has any
     *
     * @param bool $throwNoError
     * @throws SocketException
     */
    private function throwLastError(bool $throwNoError = false)
    {
        if (!$this->socket) {
            return;
        }

        $errno = socket_last_error($this->socket);

        if (!$errno) {
            if ($throwNoError) {
                throw new SocketException('Socket error: Unknown error');
            }

            return;
        }

        socket_clear_error();

        throw new SocketException(sprintf('Socket error: [%s] %s', $errno, socket_strerror($errno)));
    }

    public function disconnect(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        socket_close($this->socket);
        unset($this->socket);

        return true;
    }

    public function isConnected(): bool
    {
        return (bool)$this->socket;
    }
}
