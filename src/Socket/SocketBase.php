<?php


namespace xobotyi\beansclient\Socket;


abstract class SocketBase
{
    /**
     * @var string
     */
    protected $host;
    /**
     * @var int
     */
    protected $port;
    /**
     * @var int
     */
    protected $connectionTimeout;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return int
     */
    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * SocketBase constructor.
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     */
    public function __construct(string $host, int $port, int $connectionTimeout)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
    }
}