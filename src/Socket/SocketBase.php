<?php declare(strict_types=1);


namespace xobotyi\beansclient\Socket;


abstract class SocketBase
{
  public function __construct(
    public string $host = 'localhost',
    public int    $port = 11300,
    public int    $connectionTimeout = 10)
  {
  }

  public function host(): string
  {
    return $this->host;
  }

  public function port(): int
  {
    return $this->port;
  }

  public function connectionTimeout(): int
  {
    return $this->connectionTimeout;
  }
}
