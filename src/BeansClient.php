<?php
declare(strict_types=1);

namespace xobotyi\beansclient;

use xobotyi\beansclient\Exception\ClientException;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\ConnectionInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;

/**
 * Class BeansClient
 * @package xobotyi\beansclient
 */
class BeansClient
{
    public const CRLF             = "\r\n";
    public const CRLF_LEN         = 2;
    public const DEFAULT_PRIORITY = 2048;
    public const DEFAULT_DELAY    = 0;
    public const DEFAULT_TTR      = 30;


    /**
     * @var \xobotyi\beansclient\Interfaces\SerializerInterface | null
     */
    private $serializer;

    /**
     * @var \xobotyi\beansclient\Interfaces\ConnectionInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $defaultTube;
    /**
     * @var int
     */
    private $defaultTTR;
    /**
     * @var int
     */
    private $defaultDelay;
    /**
     * @var int|float
     */
    private $defaultPriority;

    /**
     * BeansClient constructor.
     *
     * @param \xobotyi\beansclient\Interfaces\ConnectionInterface      $connection
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @param string                                                   $defaultTube
     * @param int|float                                                $defaultPriority
     * @param int                                                      $defaultTTR
     * @param int                                                      $defaultDelay
     *
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function __construct(ConnectionInterface $connection, ?SerializerInterface $serializer = null,
                         ?string $defaultTube = null, $defaultPriority = self::DEFAULT_PRIORITY,
                         int $defaultTTR = self::DEFAULT_TTR, int $defaultDelay = self::DEFAULT_DELAY) {
        $this->setConnection($connection)
             ->setSerializer($serializer)
             ->setDefaultTTR($defaultTTR)
             ->setDefaultPriority($defaultPriority)
             ->setDefaultDelay($defaultDelay);

        if ($defaultTube) {
            $this->setDefaultTube($defaultTube)
                 ->useTube($defaultTube);
        }
    }

    /**
     * @param string $tubeName
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function useTube(string $tubeName): self {
        $usedTube = $this->dispatchCommand(new Command\UseTubeCommand($tubeName));

        if ($tubeName !== $usedTube) {
            throw new CommandException(sprintf("Failed to use `%s` tube, using `%s` instead", $tubeName, $usedTube));
        }

        return $this;
    }

    /**
     * @param \xobotyi\beansclient\Interfaces\CommandInterface $command
     *
     * @return mixed
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function dispatchCommand(CommandInterface $command) {
        if (!$this->connection->isActive()) {
            throw new ClientException("Unable to dispatch command, connection is not active");
        }

        $commandString = (string)$command;
        $this->connection->write($commandString . self::CRLF);

        $responseHeaders = $this->connection->readLine();

        if (!$responseHeaders) {
            throw new CommandException(sprintf("Got nothing in response to `%s`", $commandString));
        }

        $responseHeaders = explode(' ', $responseHeaders);

        // if error response - throw
        if (Response::ERROR_RESPONSES[$responseHeaders[0]] ?? false) {
            throw new CommandException(sprintf("Got error `%s` in response to `%s`", $responseHeaders[0], $commandString));
        }

        $data = null;

        // if data response - read it
        if (Response::DATA_RESPONSES[$responseHeaders[0]] ?? false) {
            if (($responseHeaders[1] ?? null) === null) {
                throw new ClientException(sprintf("Missing data length in response to `%s` [%s]",
                                                  $commandString,
                                                  implode(' ', $responseHeaders)));
            }

            $dataLength = (int)$responseHeaders[count($responseHeaders) - 1];

            $data = $this->connection->read($dataLength);
            $crlf = $this->connection->read(self::CRLF_LEN);

            if ($crlf !== self::CRLF) {
                throw new ClientException(sprintf('Expected CRLF (%s) after %u byte(s) of data, got `%s`',
                                                  str_replace(["\r", "\n", "\t"],
                                                              ["\\r", "\\n", "\\t",],
                                                              self::CRLF),
                                                  $dataLength,
                                                  str_replace(["\r", "\n", "\t"],
                                                              ["\\r", "\\n", "\\t"],
                                                              $crlf)));
            }
        }

        return $command->processResponse($responseHeaders, $data);
    }

    /**
     * @return string
     */
    public
    function getDefaultTube() {
        return $this->defaultTube;
    }

    /**
     * @param string $defaultTube
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setDefaultTube(string $defaultTube): self {
        if (!($defaultTube = trim($defaultTube))) {
            throw new ClientException('Default tube name has to be a valuable string');
        }

        $this->defaultTube = $defaultTube;

        return $this;
    }

    /**
     * @return int
     */
    public
    function getDefaultTTR(): int {
        return $this->defaultTTR;
    }

    /**
     * @param int $defaultTTR
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setDefaultTTR(int $defaultTTR): self {
        if ($defaultTTR < 0) {
            throw new ClientException('Default TTR has to be >= 0');
        }

        $this->defaultTTR = $defaultTTR;

        return $this;
    }

    /**
     * @return int
     */
    public
    function getDefaultDelay(): int {
        return $this->defaultDelay;
    }

    /**
     * @param int $defaultDelay
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setDefaultDelay(int $defaultDelay): self {
        if ($defaultDelay < 0) {
            throw new ClientException('Default delay has to be >= 0');
        }

        $this->defaultDelay = $defaultDelay;

        return $this;
    }

    /**
     * @return int|float
     */
    public
    function getDefaultPriority() {
        return $this->defaultPriority;
    }

    /**
     * @param int|float $defaultPriority
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setDefaultPriority($defaultPriority): self {

        if (!is_numeric($defaultPriority)) {
            throw new ClientException(sprintf('Default priority has to be a number, got %s', gettype($defaultPriority)));
        }

        if ($defaultPriority < CommandInterface::PRIORITY_MINIMUM) {
            throw new ClientException(sprintf('Default priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, $defaultPriority));
        }

        if ($defaultPriority > CommandInterface::PRIORITY_MAXIMUM) {
            throw new ClientException(sprintf('Default priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, $defaultPriority));
        }

        $this->defaultPriority = $defaultPriority;

        return $this;
    }

    /**
     * @return \xobotyi\beansclient\Interfaces\ConnectionInterface
     */
    public
    function getConnection(): ConnectionInterface {
        return $this->connection;
    }

    /**
     * @param \xobotyi\beansclient\Interfaces\ConnectionInterface $connection
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setConnection(ConnectionInterface $connection): self {
        if (!$connection->isActive()) {
            throw new ClientException('Unable to set inactive connection');
        }

        $this->connection = $connection;

        return $this;
    }

    /**
     * @return null|\xobotyi\beansclient\Interfaces\SerializerInterface
     */
    public
    function getSerializer(): ?SerializerInterface {
        return $this->serializer;
    }

    /**
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @return \xobotyi\beansclient\BeansClient
     */
    public
    function setSerializer(?SerializerInterface $serializer): self {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param int       $jobId
     * @param int|float $priority
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function bury(int $jobId, $priority = null): bool {
        $priority = is_null($priority) ? $this->defaultPriority : $priority;

        return $this->dispatchCommand(new Command\BuryCommand($jobId, $priority));
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function delete(int $jobId): bool {
        return $this->dispatchCommand(new Command\DeleteCommand($jobId));
    }

    /**
     * @param string $tubeName
     *
     * @return null|int
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function ignore(string $tubeName): ?int {
        return $this->dispatchCommand(new Command\IgnoreTubeCommand($tubeName));
    }

    /**
     * @param int $count
     *
     * @return int
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function kick(int $count): int {
        return $this->dispatchCommand(new Command\KickCommand($count));
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function kickJob(int $jobId): bool {
        return $this->dispatchCommand(new Command\KickJobCommand($jobId));
    }

    /**
     * @return array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function listTubes(): array {
        return $this->dispatchCommand(new Command\ListTubesCommand());
    }

    /**
     * @return array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function listWatchedTubes(): array {
        return $this->dispatchCommand(new Command\ListTubesWatchedCommand());
    }

    /**
     * @return array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function listUsedTubes(): array {
        return $this->dispatchCommand(new Command\ListTubeUsedCommand());
    }

    /**
     * @param string    $tubeName
     * @param int|float $delay
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function pause(string $tubeName, $delay): bool {
        return $this->dispatchCommand(new Command\PauseCommand($tubeName, $delay));
    }

    /**
     * @param $subject
     *
     * @return null|array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function peek($subject): ?array {
        return $this->dispatchCommand(new Command\PeekCommand($subject, $this->serializer ?: null));
    }

    /**
     * @param     $payload
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     *
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function put($payload, $priority = null, ?int $delay = null, ?int $ttr = null): Job {
        $priority = is_null($priority) ? $this->defaultPriority : $priority;
        $delay    = is_null($delay) ? $this->defaultDelay : $delay;
        $ttr      = is_null($ttr) ? $this->defaultTTR : $ttr;

        $commandData = $this->dispatchCommand(new Command\PutCommand($payload, $priority, $delay, $ttr, $this->serializer ?: null));

        if($commandData['status'] ?? null){
            $commandData['status'] = mb_strtolower($commandData['status']);

            if($commandData['status'] === 'inserted'){
                $commandData['status'] = Job::STATE_READY;
            }
        }

        return new Job($this, $commandData['id'] ?? null, $commandData['status'] ?? null);
    }

    /**
     * @param int $jobId
     * @param int $priority
     * @param int $delay
     *
     * @return null|string
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function release(int $jobId, $priority = null, ?int $delay = null): ?string {
        $priority = is_null($priority) ? $this->defaultPriority : $priority;
        $delay    = is_null($delay) ? $this->defaultDelay : $delay;
        return $this->dispatchCommand(new Command\ReleaseCommand($jobId, $priority, $delay));
    }

    /**
     * @param int $timeout
     *
     * @return null|\xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function reserve(int $timeout = 0): ?Job {
        $result = $this->dispatchCommand(new Command\ReserveCommand($timeout, $this->serializer ?: null));

        if (!$result) {
            return null;
        }

        return new Job($this, $result['id'], Job::STATE_RESERVED, $result['payload']);
    }

    /**
     * @return array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function stats(): array {
        return $this->dispatchCommand(new Command\StatsCommand());
    }

    /**
     * @param int $jobId
     *
     * @return null|array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function statsJob(int $jobId): ?array {
        return $this->dispatchCommand(new Command\StatsJobCommand($jobId));
    }

    /**
     * @param string $tubeName
     *
     * @return null|array
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function statsTube(string $tubeName): ?array {
        return $this->dispatchCommand(new Command\StatsTubeCommand($tubeName));
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function touch(int $jobId): bool {
        return $this->dispatchCommand(new Command\TouchCommand($jobId));
    }

    /**
     * @param string $tubeName
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function watchTube(string $tubeName): self {
        $this->dispatchCommand(new Command\WatchTubeCommand($tubeName));

        return $this;
    }
}
