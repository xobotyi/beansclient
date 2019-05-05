<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Interfaces\SocketInterface;

/**
 * Class SocketBase
 * @package xobotyi\beansclient\Socket
 */
class SocketBase implements SocketInterface
{
    public const CONNECTION_TIMEOUT = 1;
    public const READ_TIMEOUT = 1;
    public const WRITE_RETRIES = 5;

    /**
     * @var resource|null
     */
    protected $socket = null;

    public
    function __destruct() {
        $this->close();
    }

    /**
     * Reads up to $bytes bytes from the socket
     *
     * @param int $bytes Amount of bytes to read
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function read(int $bytes): string {
        $this->checkClosed();
        error_clear_last();

        $result = '';
        $bytesRead = 0;

        while ($bytesRead < $bytes) {
            $read = fread($this->socket, $bytes - $bytesRead);

            if ($read === false) {
                $this->throwLastError();
            }

            $result .= $read;
            $bytesRead += $read = mb_strlen($read, '8bit');
        }

        return $result;
    }

    /**
     * Reads up to newline from socket
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function readLine(): string {
        $this->checkClosed();
        error_clear_last();

        $result = fgets($this->socket, 8192);

        if ($result === false) {
            $this->throwLastError();
        }

        return rtrim($result);
    }

    /**
     * Writes data to the socket
     *
     * @param string $data String to write into the socket
     *
     * @return $this
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    public
    function write(string $data) {
        $this->checkClosed();
        error_clear_last();

        $retries = 0;
        $writtenTotal = 0;
        $toWrite = strlen($data);

        while (($writtenTotal < $toWrite) && ($retries < static::WRITE_RETRIES)) {
            $written = fwrite($this->socket, mb_substr($data, $writtenTotal, null, '8bit'));

            if ($written === false) {
                $this->throwLastError();
            }

            $writtenTotal += $written;

            if (++$retries === static::WRITE_RETRIES) {
                throw new SocketException(sprintf("Failed to write data to socket after %u retries (%u:%u)", static::WRITE_RETRIES, $this->host, $this->port));
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public
    function close() {
        if (isset($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public
    function isClosed(): bool {
        return !$this->socket;
    }

    /**
     * @return $this
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    private
    function checkClosed() {
        if (!$this->socket) {
            throw new SocketException("Socked is closed");
        }

        return $this;
    }

    /**
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    private
    function throwLastError() {
        if ($err = error_get_last()) {
            throw new SocketException($err['message'], $err['type']);
        }

        throw new SocketException("Unknown error");
    }
}