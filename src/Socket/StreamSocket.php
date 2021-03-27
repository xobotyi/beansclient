<?php


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Interfaces\SocketInterface;

class StreamSocket extends SocketBase implements SocketInterface
{
    private $socket;

    /**
     * StreamSocket constructor.
     * @param string $host
     * @param int $port
     * @param int|null $connectionTimeout
     *
     * @throws SocketException
     */
    public function __construct(string $host, int $port, ?int $connectionTimeout = null)
    {
        parent::__construct($host, $port, $connectionTimeout ?? 10);

        if ($port>0){
            $hostname = gethostbynamel($this->host);
            if (empty($hostname)) {
                throw new SocketException(sprintf('Host `%s` not exists or unreachable', $this->host));
            }
    
            $uri = "tcp://{$hostname[0]}:{$this->port}";    
        } else {
            $uri=$host;    
        }

        $this->socket = @stream_socket_client($uri, $errno, $errstr, $connectionTimeout, STREAM_CLIENT_CONNECT, stream_context_create());

        if ($errno || $errstr) {
            throw new SocketException(sprintf('Socket error: [%s] %s', $errno, $errstr));
        }
    }

    /**
     * @param bool $throwNoError
     * @throws SocketException
     */
    private function throwLastError(bool $throwNoError = false)
    {
        if (!$this->socket) {
            return;
        }

        $error = error_get_last();

        if (!$error) {
            if ($throwNoError) {
                throw new SocketException('Socket error: Unknown error');
            }

            return;
        }

        error_clear_last();

        throw new SocketException(sprintf('Socket error: [%s] %s', $error['type'], $error['message']));
    }

    /**
     * @inheritDoc
     * @throws SocketException
     */
    public function write(string $data): int
    {
        $this->throwIfClosed();

        $bytesToWrite = mb_strlen($data, '8BIT');
        $bytesWritten = 0;

        $attempts = 0;

        // write until it writes all the data
        while (!empty($data) && $attempts < 10) {
            $writtenBytes = fwrite($this->socket, $data);

            if ($writtenBytes === false) {
                $this->throwLastError();
            } else if ($writtenBytes === 0) {
                $attempts++;
                continue;
            }

            $attempts = 0;
            $bytesWritten += $writtenBytes;
            $data = mb_substr($data, $writtenBytes, null, '8BIT');
        }

        if ($bytesToWrite !== $bytesWritten) {
            throw new SocketException(sprintf('Failed to write data to socket after %s attempts', $attempts));
        }

        return $bytesToWrite;
    }

    /**
     * @inheritDoc
     * @throws SocketException
     */
    public function read(int $length): string
    {
        $this->throwIfClosed();

        $result = "";
        $bytesRead = 0;

        while ($bytesRead < $length) {
            $chunk = fread($this->socket, $length - $bytesRead);

            if ($chunk === false) {
                $this->throwLastError();
            }

            $result .= $chunk;
            $bytesRead = mb_strlen($result, '8BIT');
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws SocketException
     */
    public function readLine(): string
    {
        $this->throwIfClosed();

        // fgets sucks because it return false when nothing to return but this implementation may have other error
        // it is impossible to determine if we reached EOL or 8192 symbols length limit, hope 8192 will be enough
        // for beanstalkd responses
        $result = stream_get_line($this->socket, 8192, "\n");

        if ($result === false) {
            $this->throwLastError();
        }

        return rtrim($result);
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
     * @inheritDoc
     */
    public function disconnect(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        unset($this->socket);

        return true;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @inheritDoc
     */
    public function isConnected(): bool
    {
        return (bool)$this->socket;
    }
}