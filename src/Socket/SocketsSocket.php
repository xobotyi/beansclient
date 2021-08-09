<?php declare(strict_types=1);


namespace xobotyi\beansclient\Socket;


use xobotyi\beansclient\Exceptions\SocketException;
use xobotyi\beansclient\Interfaces\SocketInterface;

class SocketsSocket extends SocketBase implements SocketInterface
{
  private \Socket|null $socket = null;

  /**
   * @throws SocketException
   */
  public function __construct(string $host = 'localhost', int $port = 11300, int $connectionTimeout = 10)
  {
    parent::__construct(host: $host, port: $port, connectionTimeout: $connectionTimeout);

    $this->connect();
  }

  /**
   * @throws SocketException
   */
  public function __destruct()
  {
    $this->disconnect();
  }

  /**
   * Throws last error occurred on socket if there is any.
   *
   * @throws SocketException
   */
  public function throwLastError()
  {
    if (!$this->socket) {
      return;
    }

    $errno = socket_last_error($this->socket);
    socket_clear_error();

    if (!$errno) {
      return;
    }

    throw new SocketException(sprintf('Socket error: [%s] %s', $errno, socket_strerror($errno)));
  }

  /**
   * @inheritdoc
   */
  public function isConnected(): bool
  {
    return $this->socket !== null;
  }

  /**
   * @inheritdoc
   *
   * @throws SocketException in case of calling on already opened connection.
   */
  public function connect(): self
  {
    if ($this->isConnected()) {
      throw new SocketException('Multiple connection attempts, socket connection already established');
    }

    $hostname = $this->host;
    $domain   = AF_UNIX;

    # port less than 0 means that unix sockets should be used, otherwise - IPv4
    if ($this->port >= 0) {
      $ips = gethostbynamel($hostname);
      if ($ips === false) {
        throw new SocketException(sprintf("Could not resolve hostname `%s`", $hostname));
      }

      $hostname = $ips[0];
      $domain   = AF_INET;
    }

    $this->socket = socket_create($domain, SOCK_STREAM, SOL_TCP);
    if ($this->socket === false) {
      $this->throwLastError();
      throw new SocketException('Unknown socket error occurred during `connect`');
    }

    $SNDTIMEO = socket_get_option($this->socket, SOL_SOCKET, SO_SNDTIMEO);
    $RCVTIMEO = socket_get_option($this->socket, SOL_SOCKET, SO_RCVTIMEO);

    socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);
    # set write timeout
    socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
    # set read timeout
    socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

    if (socket_set_block($this->socket) === false) {
      $this->throwLastError();
      throw new SocketException('Failed to set blocking mode on a socket');
    }

    if (@socket_connect($this->socket, $hostname, $this->port) === false) {
      $this->throwLastError();
      throw new SocketException('Unknown socket error occurred during `socket_connect`');
    }

    # reset write timeout back to default
    socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $SNDTIMEO);
    # reset read timeout back to default
    socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $RCVTIMEO);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function disconnect(): bool
  {
    if (!$this->isConnected()) {
      return false;
    }

    socket_close($this->socket);

    $this->socket = null;

    return true;
  }

  /**
   * @inheritdoc
   *
   * @throws SocketException
   */
  public function read(int $bytes): string
  {
    if (!$this->isConnected()) {
      throw new SocketException('Unable to `read` on closed socket connection, perform `connect` first.');
    }

    $result    = "";
    $bytesRead = 0;

    while ($bytesRead < $bytes) {
      $chunk = socket_read($this->socket, $bytes - $bytesRead);
      if ($chunk === false) {
        $this->throwLastError();
        throw new SocketException('Unknown socket error occurred during `read`');
      }

      $result    .= $chunk;
      $bytesRead = mb_strlen($result, '8BIT');
    }

    return $result;
  }

  /**
   * @inheritdoc
   *
   * @throws SocketException
   */
  public function readline(): string
  {
    if (!$this->isConnected()) {
      throw new SocketException('Unable to `readline` on closed socket connection, perform `connect` first.');
    }

    $result = "";

    while (true) {
      $chunk = socket_read($this->socket, 1);
      if ($chunk === false) {
        $this->throwLastError();
        throw new SocketException('Unknown socket error occurred during `readline`');
      }

      $result .= $chunk;
      if ($chunk === "\n") {
        break;
      }
    }

    return rtrim($result);
  }

  /**
   * @inheritdoc
   *
   * @throws SocketException
   */
  public function write(string $data): int
  {
    if (!$this->isConnected()) {
      throw new SocketException('Unable to `write` on closed socket connection, perform `connect` first.');
    }

    $bytesToWrite = mb_strlen($data, '8BIT');
    $bytesWritten = 0;

    $attempts = 0;

    # write until we write all data or reach attempts limit
    while ($bytesToWrite !== $bytesWritten && $attempts < 10) {
      $bytes = socket_write($this->socket, $data);

      if ($bytes === false) {
        $this->throwLastError();
        throw new SocketException('Unknown socket error occurred during `write`');
      } else if ($bytes === 0) {
        $attempts++;
        continue;
      }

      $attempts     = 0;
      $bytesWritten += $bytes;
      $data         = mb_substr($data, $bytes, encoding: '8BIT');
    }

    if ($bytesToWrite !== $bytesWritten) {
      throw new SocketException(sprintf('Failed to write data to a socket after %d attempts', $attempts));
    }

    return $bytesWritten;
  }
}
