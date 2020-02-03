<?php


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exception\SocketException;
use xobotyi\beansclient\Exception\SocketFactoryException;
use xobotyi\beansclient\Interfaces\SocketFactoryInterface;
use xobotyi\beansclient\Interfaces\SocketInterface;

class SocketFactory implements SocketFactoryInterface
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
     * @var bool
     */
    protected $persistent;

    /**
     * @var int
     */
    protected $implementation;

    public const IMPL_AUTODETECT = 0;
    public const IMPL_SOCKETS = 1;
    public const IMPL_STREAM = 2;

    /**
     * SocketFactory constructor.
     *
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout null value defaults it to 10
     * @param int $implementation
     * @throws SocketFactoryException
     */
    public function __construct(string $host, int $port, ?int $connectionTimeout = null, int $implementation = self::IMPL_AUTODETECT)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout ?? 10;

        $this->setImplementation($implementation);
    }

    /**
     * @param int $implementation
     * @return $this
     * @throws SocketFactoryException
     */
    public function setImplementation(int $implementation)
    {
        if (self::IMPL_AUTODETECT === $implementation) {
            if (extension_loaded('sockets')) {
                $this->implementation = self::IMPL_SOCKETS;
            } else if (function_exists('stream_socket_client')) {
                $this->implementation = self::IMPL_STREAM;
            }
        } else {
            $this->checkImplementationAvailability($implementation);

            $this->implementation = $implementation;
        }

        return $this;
    }

    /**
     * @param int $implementation
     * @throws SocketFactoryException
     */
    private function checkImplementationAvailability(int $implementation): void
    {
        switch ($implementation) {
            case self::IMPL_SOCKETS:
                if (!extension_loaded('sockets')) {
                    throw new SocketFactoryException('Extension `sockets` is not available in current environment');
                }

                return;

            case self::IMPL_STREAM:
                if (!function_exists('stream_socket_client')) {
                    throw new SocketFactoryException('Stream sockets are not available in current environment');
                }

                return;

            default:
                throw new SocketFactoryException(sprintf('Unknown implementation code `%s`', $implementation));
        }
    }

    public function getImplementation(): int
    {
        return $this->implementation;
    }

    /**
     * @return SocketInterface
     * @throws SocketFactoryException
     * @throws SocketException
     */
    public function createSocket(): SocketInterface
    {
        switch ($this->implementation) {
            case self::IMPL_SOCKETS:
                return new SocketSocket($this->host, $this->port, $this->connectionTimeout);


            default:
                throw new SocketFactoryException(sprintf('Implementation `%s` is not supported', $this->implementation));
        }
    }
}