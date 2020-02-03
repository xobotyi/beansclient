<?php


namespace xobotyi\beansclient\Interfaces;


/**
 * Interface Connection
 *
 * @package xobotyi\beansclient\Interfaces
 */
interface ConnectionInterface
{
    /**
     * Connection constructor.
     *
     * @param string $host
     * @param int $port
     * @param int|null $connectionTimeout
     */
    public
    function __construct(string $host = 'localhost', int $port = -1, int $connectionTimeout = null);

    /**
     * @return bool
     */
    public
    function disconnect(): bool;

    /**
     * @return string
     */
    public
    function getHost(): ?string;

    /**
     * @return int
     */
    public
    function getPort(): ?int;

    /**
     * @return int
     */
    public
    function getConnectionTimeout(): ?int;

    /**
     * @return bool
     */
    public
    function isActive(): bool;

    /**
     * @return bool
     */
    public
    function isPersistent(): bool;

    /**
     * @param int $length
     * @return string
     */
    public
    function read(int $length): string;

    /**
     * @return string
     */
    public
    function readLine(): string;

    /**
     * @param string $str
     *
     * @return int Bytes written
     */
    public
    function write(string $str): int;
}
