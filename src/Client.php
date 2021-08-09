<?php declare(strict_types=1);


namespace xobotyi\beansclient;


use JetBrains\PhpStorm\ArrayShape;
use xobotyi\beansclient\Exceptions\ClientException;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Interfaces\SocketInterface;

class Client
{
  /**
   * @throws ClientException
   */
  public function __construct(
    private SocketInterface      $socket,
    private ?SerializerInterface $serializer = null,
    ?string                      $defaultTube = null,
    private int|float            $defaultPriority = 1_024,
    private int|float            $defaultTTR = 30,
    private int|float            $defaultDelay = 0,
    private int|float            $maxPayloadSize = 65_536
  )
  {
    $this->setSocket($socket)
         ->setSerializer($serializer)
         ->setDefaultPriority($defaultPriority)
         ->setDefaultDelay($defaultDelay)
         ->setDefaultTTR($defaultTTR);

    if ($defaultTube) {
      $this->useTube($defaultTube);
      $this->watchTube($defaultTube);
    }
  }

  public function socket(): SocketInterface
  {
    return $this->socket;
  }

  /**
   * @throws ClientException
   */
  public function setSocket(SocketInterface $connection): self
  {
    if (!$connection->isConnected()) {
      throw new ClientException("Unable to use closed socket");
    }

    $this->socket = $connection;

    return $this;
  }

  public function serializer(): ?SerializerInterface
  {
    return $this->serializer;
  }

  public function setSerializer(?SerializerInterface $serializer): self
  {
    $this->serializer = $serializer;

    return $this;
  }

  public function defaultPriority(): int
  {
    return $this->defaultPriority;
  }

  public function setDefaultPriority(int $priority): self
  {
    Beanstalkd::validatePriority($priority);

    $this->defaultPriority = $priority;

    return $this;
  }

  public function defaultTTR(): int
  {
    return $this->defaultTTR;
  }

  public function setDefaultTTR(int $ttr): self
  {
    Beanstalkd::validateTTR($ttr);

    $this->defaultTTR = $ttr;

    return $this;
  }

  public function defaultDelay(): int
  {
    return $this->defaultDelay;
  }

  public function setDefaultDelay(int $delay): self
  {
    Beanstalkd::validateDelay($delay);

    $this->defaultDelay = $delay;

    return $this;
  }

  private function serializePayload(mixed $data): ?string
  {
    if ($data === null) {
      return null;
    }

    if ($this->serializer) {
      $data = $this->serializer->serialize($data);
    } else if (!is_string($data)) {
      throw new \InvalidArgumentException(
        sprintf(
          'Serializer not defined, payload has to be string, got `%s`. Configure serializer or serialize payload manually.',
          gettype($data)
        )
      );
    }

    $payloadSize = mb_strlen($data, '8BIT');
    if ($payloadSize > $this->maxPayloadSize) {
      throw new \InvalidArgumentException(
        sprintf(
          '%s is too big, maximum size is %d bytes, got %d',
          $this->serializer ? 'Serialized payload' : 'Payload',
          $this->maxPayloadSize,
          $payloadSize
        )
      );
    }

    return $data;
  }

  /**
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  #[ArrayShape(["status" => "string", "headers" => "string[]", 'data' => "null|string",])]
  private function dispatchCommand(Command $cmd, array $args = [], mixed $payload = null): array
  {
    # write command to a socket
    $this->socket->write($cmd->buildCommand($args, $this->serializePayload($payload)));

    # read first line that contains most of the infos
    $headers = Beanstalkd::parseResponseHeaders($this->socket->readline());

    $response = [
      "status" => $headers['status'],
      "headers" => $headers['headers'],
      "data" => null,
    ];

    if ($headers['hasData']) {
      $response['data'] = $this->socket->read($headers['dataLength'] + Beanstalkd::CRLF_LEN);
    }

    return $cmd->handleResponse($response, $this->serializer);
  }

  ## COMMANDS

  /**
   * Subsequent put commands will put jobs into the tube specified by this command. If no use
   * command has been issued, jobs will be put into the tube named "default".
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function useTube(string $tubeName): string
  {
    Beanstalkd::validateTubeName($tubeName);

    $cmd = Commander::getCommand(Beanstalkd::CMD_USE);

    $result = $this->dispatchCommand($cmd, [$tubeName]);

    return $result['headers'][0];
  }

  /**
   * This command for any process that wants to insert a job into the queue.
   *
   * @param $payload - Payload of the job. Non string or integer values will be serialized with
   * [[IClientCtorOptions.serializer]]. Byte size of payload should be less than less than server's
   * max-job-size (default: 2**16) and client's [[IClientCtorOptions.maxPayloadSize]].
   *
   * @param $ttr - Time to run -- is an integer number of seconds to allow a worker
   * to run this job. This time is counted from the moment a worker reserves
   * this job. If the worker does not delete, release, or bury the job within
   * <ttr> seconds, the job will time out and the server will release the job.
   * The minimum ttr is 1. Maximum ttr is 2**32-1.
   *
   * @param $priority - Integer < 2**32. Jobs with smaller priority values will be
   * scheduled before jobs with larger priorities. The most urgent priority is 0;
   * the least urgent priority is 4,294,967,295.
   *
   * @param $delay - Integer number of seconds to wait before putting the job in
   * the ready queue. The job will be in the "delayed" state during this time.
   * Maximum delay is 2**32-1.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   * @throws ClientException
   */
  #[ArrayShape(["id" => "int", "state" => "string"])]
  public function put(mixed $payload, int $ttr = null, int $priority = null, int $delay = null): array
  {
    $ttr      ??= $this->defaultTTR;
    $priority ??= $this->defaultPriority;
    $delay    ??= $this->defaultDelay;

    Beanstalkd::validateTTR($ttr);
    Beanstalkd::validatePriority($priority);
    Beanstalkd::validateDelay($delay);

    $cmd = Commander::getCommand(Beanstalkd::CMD_PUT);

    $result = $this->dispatchCommand($cmd, [$priority, $delay, $ttr], $payload);

    if ($result['status'] === Beanstalkd::STATUS_JOB_TOO_BIG) {
      throw new ClientException("Provided job payload exceeds maximal server's `max-job-size` config");
    }

    if ($result['status'] === Beanstalkd::STATUS_EXPECTED_CRLF) {
      throw new ClientException('Missing trailing CRLF');
    }

    if ($result['status'] === Beanstalkd::STATUS_DRAINING) {
      throw new ClientException('Server is in `drain mode` and no longer accepting new jobs.',);
    }

    $state = $delay !== 0 ? Beanstalkd::JOB_STATE_DELAYED : Beanstalkd::JOB_STATE_READY;

    return [
      'id' => (int)$result['headers'][0],
      'state' => $result['status'] === Beanstalkd::STATUS_BURIED ? Beanstalkd::JOB_STATE_BURIED : $state,
    ];
  }

  /**
   * This will return a newly-reserved job. If no job is available to be reserved,
   * beanstalkd will wait to send a response until one becomes available. Once a
   * job is reserved for the client, the client has limited time to run (TTR) the
   * job before the job times out. When the job times out, the server will put the
   * job back into the ready queue. Both the TTR and the actual time left can be
   * found in response to the [[Client.statsJob]] command.
   *
   * If more than one job is ready, beanstalkd will choose the one with the
   * smallest priority value. Within each priority, it will choose the one that
   * was received first.
   *
   * During the TTR of a reserved job, the last second is kept by the server as a
   * safety margin, during which the client will not be made to wait for another
   * job. If the client issues a reserve command during the safety margin, or if
   * the safety margin arrives while the client is waiting on a reserve command,
   * the server will respond with: DEADLINE_SOON
   *
   * This gives the client a chance to delete or release its reserved job before
   * the server automatically releases it.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   * @throws Exceptions\ClientException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function reserve(): ?array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_RESERVE);

    $result = $this->dispatchCommand($cmd);

    if ($result['status'] === Beanstalkd::STATUS_TIMED_OUT) {
      return null;
    }

    if ($result['status'] === Beanstalkd::STATUS_DEADLINE_SOON) {
      throw new ClientException("One of jobs reserved by this client will reach deadline soon, release it first.");
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * Same as [[Client.reserve]] but with limited amount of time to wait for the job.
   *
   * A timeout value of 0 will cause the server to immediately return either a
   * response or TIMED_OUT. A positive value of timeout will limit the amount of
   * time the client will block on the reserve request until a job becomes
   * available.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   * @throws ClientException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function reserveWithTimeout(int $timeout): ?array
  {
    Beanstalkd::validateTimeout($timeout);

    $cmd = Commander::getCommand(Beanstalkd::CMD_RESERVE_WITH_TIMEOUT);

    $result = $this->dispatchCommand($cmd, [$timeout]);

    if ($result['status'] === Beanstalkd::STATUS_TIMED_OUT) {
      return null;
    }

    if ($result['status'] === Beanstalkd::STATUS_DEADLINE_SOON) {
      throw new ClientException("One of jobs reserved by this client will reach deadline soon, release it first.");
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * A job can be reserved by its id. Once a job is reserved for the client,
   * the client has limited time to run (TTR) the job before the job times out.
   * When the job times out, the server will put the job back into the ready queue.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   * @throws ClientException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function reserveJob(int $jobId): ?array
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_RESERVE_JOB);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    if ($result['status'] === Beanstalkd::STATUS_DEADLINE_SOON) {
      throw new ClientException("One of jobs reserved by this client will reach deadline soon, release it first.");
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * The delete command removes a job from the server entirely. It is normally used
   * by the client when the job has successfully run to completion. A client can
   * delete jobs that it has reserved, ready jobs, delayed jobs, and jobs that are
   * buried.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function delete(int $jobId): bool
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_DELETE);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    return $result['status'] === Beanstalkd::STATUS_DELETED;
  }

  /**
   * The release command puts a reserved job back into the ready queue (and marks
   * its state as "ready") to be run by any client. It is normally used when the job
   * fails because of a transitory error.
   *
   * @param $jobId - job id to release.
   * @param $priority - a new priority to assign to the job.
   * @param $delay - integer number of seconds to wait before putting the job in
   * the ready queue. The job will be in the "delayed" state during this time.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public
  function release(int $jobId, int $priority = null, int $delay = null): ?string
  {
    $priority ??= $this->defaultPriority;
    $delay    ??= $this->defaultDelay;

    Beanstalkd::validateJobId($jobId);
    Beanstalkd::validatePriority($priority);
    Beanstalkd::validateDelay($delay);

    $cmd = Commander::getCommand(Beanstalkd::CMD_RELEASE);

    $result = $this->dispatchCommand($cmd, [$jobId, $priority, $delay]);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    if ($result['status'] === Beanstalkd::STATUS_BURIED) {
      return Beanstalkd::JOB_STATE_BURIED;
    }

    return $delay !== 0 ? Beanstalkd::JOB_STATE_DELAYED : Beanstalkd::JOB_STATE_READY;
  }

  /**
   * The bury command puts a job into the "buried" state. Buried jobs are put into a
   * FIFO linked list and will not be touched by the server again until a client
   * kicks them with the [[Client.kick]] command
   *
   * @param $jobId - job id to bury.
   * @param $priority - a new priority to assign to the job.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function bury(int $jobId, int $priority = null): bool
  {
    $priority ??= $this->defaultPriority;

    Beanstalkd::validateJobId($jobId);
    Beanstalkd::validatePriority($priority);

    $cmd = Commander::getCommand(Beanstalkd::CMD_BURY);

    $result = $this->dispatchCommand($cmd, [$jobId, $priority]);

    return $result['status'] === Beanstalkd::STATUS_BURIED;
  }

  /**
   * The "touch" command allows a worker to request more time to work on a job.
   * This is useful for jobs that potentially take a long time, but you still want
   * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
   * may periodically tell the server that it's still alive and processing a job
   * (e.g. it may do this on DEADLINE_SOON). The command postpones the auto
   * release of a reserved job until TTR seconds from when the command is issued
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function touch(int $jobId): bool
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_TOUCH);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    return $result['status'] === Beanstalkd::STATUS_TOUCHED;
  }

  /**
   * The "watch" command adds the named tube to the watch list for the current
   * connection. A reserve command will take a job from any of the tubes in the
   * watch list. For each new connection, the watch list initially consists of one
   * tube, named "default".
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public
  function watchTube(string $tubeName): int
  {
    Beanstalkd::validateTubeName($tubeName);

    $cmd = Commander::getCommand(Beanstalkd::CMD_WATCH);

    $result = $this->dispatchCommand($cmd, [$tubeName]);

    return (int)$result['headers'][0];
  }

  /**
   * Removes the named tube from the watch list for the current connection.
   *
   * False returned in case of attempt to ignore last tube watched
   * (`NOT_IGNORED` returned from server).
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public
  function ignore(string $tubeName): bool
  {
    Beanstalkd::validateTubeName($tubeName);

    $cmd = Commander::getCommand(Beanstalkd::CMD_IGNORE);

    $result = $this->dispatchCommand($cmd, [$tubeName]);

    if ($result['status'] === Beanstalkd::STATUS_WATCHING) {
      return true;
    }

    return false;
  }

  /**
   * Inspect a job with given ID without reserving it.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function peek(int $jobId): ?array
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_PEEK);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * Inspect the next ready job. Operates only on the currently used tube.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function peekReady(): ?array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_PEEK_READY);

    $result = $this->dispatchCommand($cmd);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * Inspect the next delayed job. Operates only on the currently used tube.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function peekDelayed(): ?array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_PEEK_DELAYED);

    $result = $this->dispatchCommand($cmd);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * Inspect the next buried job. Operates only on the currently used tube.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  #[ArrayShape(["id" => "int", "payload" => "mixed"])]
  public function peekBuried(): ?array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_PEEK_BURIED);

    $result = $this->dispatchCommand($cmd);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return [
      'id' => (int)$result['headers'][0],
      'payload' => $result['data'],
    ];
  }

  /**
   * The kick command applies only to the currently used tube. It moves jobs into
   * the ready queue. If there are any buried jobs, it will only kick buried jobs.
   * Otherwise it will kick delayed jobs.
   *
   * @param $bound - integer upper bound on the number of jobs to kick. The server
   * will kick no more than <bound> jobs.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function kick(int $bound): int
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_KICK);

    $result = $this->dispatchCommand($cmd, [$bound]);

    return (int)$result['headers'][0];
  }

  /**
   * The kick-job command is a variant of kick that operates with a single job
   * identified by its job id. If the given job id exists and is in a buried or
   * delayed state, it will be moved to the ready queue of the the same tube where it
   * currently belongs.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function kickJob(int $jobId): bool
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_KICK_JOB);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    return $result['status'] === Beanstalkd::STATUS_KICKED;
  }

  /**
   * The stats command gives statistical information about the system as a whole.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function stats(): array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_STATS);

    $result = $this->dispatchCommand($cmd);

    return Beanstalkd::processStats($result['data']);
  }

  /**
   * The stats-tube command gives statistical information about the specified tube
   * if it exists.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function statsTube(string $tubeName): ?array
  {
    Beanstalkd::validateTubeName($tubeName);

    $cmd = Commander::getCommand(Beanstalkd::CMD_STATS_TUBE);

    $result = $this->dispatchCommand($cmd, [$tubeName]);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return Beanstalkd::processTubeStats($result['data']);
  }

  /**
   * The stats-job command gives statistical information about the specified job if
   * it exists.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function statsJob(int $jobId): ?array
  {
    Beanstalkd::validateJobId($jobId);

    $cmd = Commander::getCommand(Beanstalkd::CMD_STATS_JOB);

    $result = $this->dispatchCommand($cmd, [$jobId]);

    if ($result['status'] === Beanstalkd::STATUS_NOT_FOUND) {
      return null;
    }

    return Beanstalkd::processJobStats($result['data']);
  }

  /**
   * The list-tubes command returns a list of all existing tubes.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function listTubes(): array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_LIST_TUBES);

    $result = $this->dispatchCommand($cmd);

    return $result['data'];
  }

  /**
   * The list-tube-used command returns the tube currently being used by the
   * client.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function listTubeUsed(): string
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_LIST_TUBE_USED);

    $result = $this->dispatchCommand($cmd);

    return $result['headers'][0];
  }

  /**
   * The list-tubes-watched command returns a list tubes currently being watched by
   * the client.
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function listTubesWatched(): array
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_LIST_TUBES_WATCHED);

    $result = $this->dispatchCommand($cmd);

    return $result['data'];
  }

  /**
   * The pause-tube command can delay any new job being reserved for a given time.
   *
   * @param $tubeName - tube to pause
   * @param $delay - integer number of seconds < 2**32 to wait before reserving any more
   * jobs from the queue
   *
   * @throws Exceptions\CommandException
   * @throws Exceptions\ResponseException
   */
  public function pauseTube(string $tubeName, int $delay): bool
  {
    Beanstalkd::validateTubeName($tubeName);
    Beanstalkd::validateDelay($delay);

    $cmd = Commander::getCommand(Beanstalkd::CMD_PAUSE_TUBE);

    $result = $this->dispatchCommand($cmd, [$tubeName, $delay]);

    return $result['status'] === Beanstalkd::STATUS_PAUSED;
  }
}
