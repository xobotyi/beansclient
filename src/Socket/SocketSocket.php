<?php


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Interfaces\SocketInterface;

class SocketSocket extends SocketBase implements SocketInterface
{
    private $socket;

    private $DFLT_SO_SNDTIMEO;
    private $DFLT_SO_RCVTIMEO;

    /**
     * SocketSocket constructor.
     *
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     *
     * @throws SocketException
     */
    public function __construct(string $host, int $port, ?int $connectionTimeout = null)
    {
        parent::__construct($host, $port, $connectionTimeout ?? 10);

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$this->socket) {
            $this->throwLastError();
        }

        $this->DFLT_SO_SNDTIMEO = socket_get_option($this->socket, SOL_SOCKET, SO_SNDTIMEO);
        $this->DFLT_SO_RCVTIMEO = socket_get_option($this->socket, SOL_SOCKET, SO_RCVTIMEO);

        socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        $this->setWriteTimeout($connectionTimeout)->setReadTimeout($connectionTimeout);

        $hostname = gethostbynamel($this->host);
        if (empty($hostname)) {
            throw new SocketException(sprintf('Host `%s` not exists or unreachable', $this->host));
        }

        if (!@socket_connect($this->socket, $hostname[0], $this->port)) {
            $this->throwLastError(true);
        }

        $this->resetWriteTimeout()->resetReadTimeout();
    }

    private function setWriteTimeout(int $timeout)
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => 0]);
        return $this;
    }

    private function resetWriteTimeout()
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $this->DFLT_SO_SNDTIMEO);
        return $this;
    }

    private function setReadTimeout(int $timeout)
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
        return $this;
    }

    private function resetReadTimeout()
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $this->DFLT_SO_RCVTIMEO);
        return $this;
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

    /**
     * {@inheritDoc}
     */
    public function disconnect(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        socket_close($this->socket);
        unset($this->socket);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        return (bool)$this->socket;
    }

    /**
     * @throws SocketException
     */
    private function throwIfClosed()
    {
        if (!$this->isConnected()) {
            throw new SocketException('Socket connection was closed');
        }
    }

    /**
     * {@inheritDoc}
     * @throws SocketException
     */
    public function read(int $length, ?int $timeout = null): string
    {
        $this->throwIfClosed();

        $result = "";
        $bytesRead = 0;

        while ($bytesRead < $length) {
            $chunk = socket_read($this->socket, $length - $bytesRead);

            if ($chunk === false) {
                $this->throwLastError();
            }

            $result .= $chunk;
            $bytesRead = mb_strlen($result, '8BIT');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @throws SocketException
     */
    public function readLine(?int $timeout = null): string
    {
        $this->throwIfClosed();

        // socket_read can stop on \r, but we need \n
        $result = "";
        while (mb_substr($result, -1, null, '8BIT') !== "\n") {
            $chunk = socket_read($this->socket, 8192, PHP_NORMAL_READ);

            if ($chunk === false) {
                $this->throwLastError();
            }

            $result .= $chunk;
        }

        return rtrim($result);
    }

    /**
     * {@inheritDoc}
     * @return number
     * @throws SocketException
     */
    public function write(string $data, ?int $timeout = null): int
    {
        $this->throwIfClosed();

        $bytesToWrite = mb_strlen($data, '8BIT');

        // write until it writes all the data
        while (!empty($data)) {
            $writtenBytes = socket_write($this->socket, $data);

            if ($writtenBytes === false) {
                $this->throwLastError();
            }

            $data = mb_substr($data, $writtenBytes, null, '8BIT');
        }

        return $bytesToWrite;
    }
}
