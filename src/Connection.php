<?php declare(strict_types=1);


namespace xobotyi\beansclient;


use xobotyi\beansclient\Interfaces\ConnectionInterface;
use xobotyi\beansclient\Interfaces\SocketInterface;
use xobotyi\beansclient\Socket\SocketsSocket;

class Connection implements ConnectionInterface
{
  private SocketInterface $socket;

  /**
   * @inheritdoc
   */
  public function __construct(string $host = 'localhost', int $port = 11300, int $connectionTimeout = 0, string $socketFQN = SocketsSocket::class)
  {
    if (!class_exists($socketFQN, true)) {
      throw new \InvalidArgumentException("Class {$socketFQN} not exists");
    }

    $this->socket = new $socketFQN($host, $port, $connectionTimeout);

    $this->socket->connect();
  }

  public function host(): string
  {
    return $this->socket->host();
  }

  public function port(): int
  {
    return $this->socket->port();
  }

  public function connectionTimeout(): int
  {
    return $this->socket->connectionTimeout();
  }

  /**
   * @inheritdoc
   */
  public function disconnect(): bool
  {
    return $this->socket->disconnect();
  }

  /**
   * @inheritdoc
   */
  public function read(int $bytes): string
  {
    return $this->socket->read($bytes);
  }

  /**
   * @inheritdoc
   */
  public function readline(): string
  {
    return $this->socket->readline();
  }

  /**
   * @inheritdoc
   */
  public function write(string $data): int
  {
    return $this->socket->write($data);
  }
}
