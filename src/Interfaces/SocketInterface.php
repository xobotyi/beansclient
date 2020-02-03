<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Interfaces;


interface SocketInterface
{
    public function getHost(): string;

    public function getPort(): int;

    public function getConnectionTimeout(): int;

    /**
     * Writes data to the socket.
     *
     * @param string $data
     * @param int|null $timeout
     * @return int
     */
    public function write(string $data, ?int $timeout = null): int;

    /**
     * Reads up to $length bytes from the socket.
     *
     * @param int $length
     * @param int|null $timeout
     * @return string
     */
    public function read(int $length, ?int $timeout = null): string;

    /**
     * Read a single line from stream. Reads up to a newline.
     * Trailing whitespace and newlines not returned.
     *
     * @param int|null $timeout
     * @return string
     */
    public function readLine(?int $timeout = null): string;

    /**
     * Disconnect the socket. Further usage will cause the exception throw.
     * Returns true if socket been connected and became disconnected now.
     *
     * @return bool
     */
    public function disconnect(): bool;

    /**
     * Returns true if socket is connected.
     *
     * @return bool
     */
    public function isConnected(): bool;
}
