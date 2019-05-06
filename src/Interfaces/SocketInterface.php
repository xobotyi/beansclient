<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Interfaces;


interface SocketInterface
{
    public
    function getHost(): ?string;

    public
    function getPort(): ?int;

    public
    function getTimeout(): ?int;

    public
    function isPersistent(): ?bool;

    public
    function read(int $bytes): string;

    public
    function readLine(): string;

    public
    function write(string $data);

    public
    function close();

    public
    function isClosed(): bool;
}