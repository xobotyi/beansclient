<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Interfaces\SocketInterface;

/**
 * Class SocketBase
 * @package xobotyi\beansclient\Socket
 */
abstract
class SocketBase implements SocketInterface
{
    public const CONNECTION_TIMEOUT = 1;
    public const READ_TIMEOUT       = 0;
    public const WRITE_RETRIES      = 5;
    public const READ_RETRIES       = 5;

    public const READ_TIMEOUT_LEEWAY = 1;

    /**
     * @var resource|null
     */
    protected $socket = null;

    /**
     * @var null | string
     */
    protected $host = null;
    /**
     * @var null | integer
     */
    protected $port = null;
    /**
     * @var null | integer
     */
    protected $timeout = null;
    /**
     * @var null | boolean
     */
    protected $persistent = null;

    /**
     * @return null|string
     */
    public
    function getHost(): ?string {
        return $this->host;
    }

    /**
     * @return null|int
     */
    public
    function getPort(): ?int {
        return $this->port;
    }

    /**
     * @return null|int
     */
    public
    function getTimeout(): ?int {
        return $this->timeout;
    }

    /**
     * @return null|bool
     */
    public
    function isPersistent(): ?bool {
        return $this->persistent;
    }

    public
    function __destruct() {
        $this->close();
    }

    /**
     * @return $this
     */
    public
    function close() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }

        return $this;
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
        $this->checkClosed();
        error_clear_last();

        $result         = '';
        $bytesReadTotal = 0;

        $emptyConsecutiveReads = 0;

        $timeout !== null && stream_set_timeout($this->socket, $timeout + static::READ_TIMEOUT_LEEWAY);
        while ($bytesReadTotal < $bytes) {
            $read = fread($this->socket, $bytes - $bytesReadTotal);

            if ($read === false) {
                $this->throwLastError();
            }

            $bytesRead = mb_strlen($read, '8bit');

            if ($bytesRead) {
                $emptyConsecutiveReads = 0;
            }
            else if (++$emptyConsecutiveReads === static::READ_RETRIES) {
                throw new SocketException(sprintf('Failed to read %u bytes from socket after %u retries, got only %u bytes (%s:%u)', $bytes, static::READ_RETRIES, $bytesReadTotal, $this->host, $this->port));
            }

            $result         .= $read;
            $bytesReadTotal += $bytesRead;
        }
        $timeout !== null && stream_set_timeout($this->socket, static::READ_TIMEOUT + static::READ_TIMEOUT_LEEWAY);

        return $result;
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
        $this->checkClosed();
        error_clear_last();

        $timeout !== null && stream_set_timeout($this->socket, $timeout + static::READ_TIMEOUT_LEEWAY);
        $result = fgets($this->socket, 8192);
        $timeout !== null && stream_set_timeout($this->socket, static::READ_TIMEOUT + static::READ_TIMEOUT_LEEWAY);

        if ($result === false) {
            $this->throwLastError();
        }

        return rtrim($result);
    }

    /**
     * @return $this
     * @throws \xobotyi\beansclient\Exception\SocketException
     */
    private
    function checkClosed() {
        if (!$this->socket) {
            throw new SocketException('Socked is closed');
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

        throw new SocketException('Unknown error');
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
        $this->checkClosed();
        error_clear_last();

        $retries      = 0;
        $writtenTotal = 0;
        $toWrite      = strlen($data);

        while (($writtenTotal < $toWrite) && ($retries < static::WRITE_RETRIES)) {
            $written = fwrite($this->socket, mb_substr($data, $writtenTotal, null, '8bit'));

            if ($written === false) {
                $this->throwLastError();
            }

            $writtenTotal += $written;

            if (++$retries === static::WRITE_RETRIES) {
                throw new SocketException(sprintf('Failed to write data to socket after %u retries (%s:%u)', static::WRITE_RETRIES, $this->host, $this->port));
            }
        }

        return $writtenTotal;
    }

    /**
     * @return bool
     */
    public
    function isClosed(): bool {
        return !$this->socket;
    }
}
