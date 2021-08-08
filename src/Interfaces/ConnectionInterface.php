<?php declare(strict_types=1);


namespace xobotyi\beansclient\Interfaces;


use xobotyi\beansclient\Exceptions\SocketException;
use xobotyi\beansclient\Socket\SocketsSocket;

interface ConnectionInterface
{
  /**
   * Creates new TCP socket connection. To use with UNIX sockets - set port to `-1`.
   *
   * @param string $host
   * @param int $port
   * @param int $connectionTimeout
   * @param string $socketFQN
   * @throws SocketException
   */
  public function __construct(string $host = 'localhost', int $port = 11300, int $connectionTimeout = 10, string $socketFQN = SocketsSocket::class);

  public function host(): string;

  public function port(): int;

  public function connectionTimeout(): int;

  /**
   * Closes socket connection.
   *
   * @return bool False in case operation was performed on closed connection.
   */
  public function disconnect(): bool;

  /**
   * Reads $bytes number of bytes from a socket
   *
   * @param int $bytes Amount of bytes to read
   * @return string
   */
  public function read(int $bytes): string;

  /**
   * Reads from a socket until '\n' is reached.
   *
   * @return string
   */
  public function readline(): string;

  /**
   * Writes data to a socket
   *
   * @param string $data Data to write
   * @return int Amount of bytes written
   */
  public function write(string $data): int;
}
