<?php declare(strict_types=1);


namespace xobotyi\beansclient\Interfaces;


use xobotyi\beansclient\Exceptions\SocketException;

interface SocketInterface
{
  /**
   * @param string $host hostname to connect to
   * @param int $port port to use. Use -1 with unix sockets.
   * @param int $connectionTimeout
   */
  public function __construct(string $host = 'localhost', int $port = 11300, int $connectionTimeout = 10);

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
   * Opens socket connection.
   *
   * @return $this
   * @throws SocketException in case of calling on already opened connection.
   */
  public function connect(): self;

  /**
   * Returns true if socket connection is open.
   *
   * @return bool
   */
  public function isConnected(): bool;

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
