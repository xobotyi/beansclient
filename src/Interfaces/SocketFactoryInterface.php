<?php


namespace xobotyi\beansclient\Interfaces;


interface SocketFactoryInterface
{
    public function __construct(string $host, int $port, int $connectionTimeout, int $implementation);

    public function createSocket(): SocketInterface;
}