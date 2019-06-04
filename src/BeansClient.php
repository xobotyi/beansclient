<?php

namespace xobotyi\beansclient;


use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;

/**
 * Class BeansClient
 *
 * @package xobotyi\beansclient
 */
class BeansClient
{
    public const CRLF             = "\r\n";
    public const CRLF_LEN         = 2;
    public const DEFAULT_PRIORITY = 2048;
    public const DEFAULT_DELAY    = 0;
    public const DEFAULT_TTR      = 30;
    public const DEFAULT_TUBE     = 'default';
    /**
     * @var Interfaces\ConnectionInterface
     */
    private $connection;
    /**
     * @var Interfaces\SerializerInterface|null
     */
    private $serializer;

    /**
     * BeansClient constructor.
     *
     * @param \xobotyi\beansclient\Interfaces\ConnectionInterface      $connection
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function __construct(Interfaces\ConnectionInterface $connection, ?Interfaces\SerializerInterface $serializer = null) {
        $this->setConnection($connection);
        $this->setSerializer($serializer);
    }

    /**
     * @param int $jobId
     * @param int $priority
     *
     * @return bool
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function bury(int $jobId, $priority = self::DEFAULT_PRIORITY): bool {
        return $this->dispatchCommand(new Command\Bury($jobId, $priority));
    }

    /**
     * @param \xobotyi\beansclient\Command\CommandAbstract $cmd
     *
     * @return mixed
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function dispatchCommand(Command\CommandAbstract $cmd) {
        $request = $cmd->getCommandStr() . self::CRLF;

        $this->connection->write($request);

        $responseHeader = explode(' ', $this->connection->readLine());

        // throwing exception if there is an error response
        if (in_array($responseHeader[0], Response::ERROR_RESPONSES)) {
            throw new Exception\JobException("Got {$responseHeader[0]} in response to {$cmd->getCommandStr()}");
        }

        // if request contains data - read it
        if (in_array($responseHeader[0], Response::DATA_RESPONSES)) {
            if (count($responseHeader) === 1) {
                throw new Exception\ClientException("Got no data length in response to {$cmd->getCommandStr()} [" . implode(' ', $responseHeader) . "]");
            }

            $data = $this->connection->read((int)$responseHeader[count($responseHeader) - 1]);
            $crlf = $this->connection->read(self::CRLF_LEN);

            if ($crlf !== self::CRLF) {
                throw new Exception\ClientException(sprintf('Expected CRLF[%s] after %u byte(s) of data, got %s',
                                                            str_replace(["\r", "\n", "\t"], [
                                                                "\\r",
                                                                "\\n",
                                                                "\\t",
                                                            ], self::CRLF),
                                                            $responseHeader[1],
                                                            str_replace(["\r", "\n", "\t"], ["\\r", "\\n", "\\t"], $crlf)));
            }
        }
        else {
            $data = null;
        }

        return $cmd->parseResponse($responseHeader, $data);
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function delete(int $jobId): bool {
        return $this->dispatchCommand(new Command\Delete($jobId));
    }

    /**
     * @return \xobotyi\beansclient\Interfaces\ConnectionInterface
     */
    public
    function getConnection(): Interfaces\ConnectionInterface {
        return $this->connection;
    }

    /**
     * @param \xobotyi\beansclient\Interfaces\ConnectionInterface $connection
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \xobotyi\beansclient\Exception\ClientException
     */
    public
    function setConnection(Interfaces\ConnectionInterface $connection): self {
        if (!$connection->isActive()) {
            throw new Exception\ClientException('Given connection is not active');
        }
        $this->connection = $connection;

        return $this;
    }

    // COMMANDS
    // jobs

    /**
     * @return null|\xobotyi\beansclient\Interfaces\SerializerInterface
     */
    public
    function getSerializer(): ?Interfaces\SerializerInterface {
        return $this->serializer;
    }

    /**
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @return \xobotyi\beansclient\BeansClient
     */
    public
    function setSerializer(?Interfaces\SerializerInterface $serializer): self {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param string $tube
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function ignoreTube(string $tube): self {
        $this->dispatchCommand(new Command\IgnoreTube($tube));

        return $this;
    }

    /**
     * @param int $count
     *
     * @return int
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function kick(int $count): int {
        return $this->dispatchCommand(new Command\Kick($count));
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function kickJob(int $jobId): bool {
        return $this->dispatchCommand(new Command\KickJob($jobId));
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function listTubeUsed(): string {
        return $this->dispatchCommand(new Command\ListTubeUsed());
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function listTubes(): array {
        return $this->dispatchCommand(new Command\ListTubes());
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function listTubesWatched(): array {
        return $this->dispatchCommand(new Command\ListTubesWatched());
    }

    /**
     * @param $subject
     *
     * @return array|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function peek($subject): ?array {
        return $this->dispatchCommand(new Command\Peek($subject, $this->serializer));
    }

    /**
     * @param     $payload
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     *
     * @return \xobotyi\beansclient\Job
     *
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function put($payload, $priority = self::DEFAULT_PRIORITY, int $delay = self::DEFAULT_DELAY, int $ttr = self::DEFAULT_TTR): Job {
        $res = $this->dispatchCommand(new Command\Put($payload, $priority, $delay, $ttr, $this->serializer));

        return $res
            ? new Job($this, $res['id'], strtolower($res['status']), $payload)
            : new Job($this, null);
    }

    // tubes

    /**
     * @param int       $jobId
     * @param int|float $priority
     * @param int       $delay
     *
     * @return string|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function release(int $jobId, $priority = self::DEFAULT_PRIORITY, int $delay = self::DEFAULT_DELAY): ?string {
        return $this->dispatchCommand(new Command\Release($jobId, $priority, $delay));
    }

    /**
     * @param int|null $timeout
     *
     * @return \xobotyi\beansclient\Job
     *
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function reserve(?int $timeout = 0): Job {
        $res = $this->dispatchCommand(new Command\Reserve($timeout, $this->serializer));

        return $res
            ? new Job($this, $res['id'], Job::STATE_RESERVED, $res['payload'])
            : new Job($this, null);
    }

    /**
     * @return array|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function stats(): ?array {
        return $this->dispatchCommand(new Command\Stats());
    }

    /**
     * @param int $jobId
     *
     * @return array|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function statsJob(int $jobId): ?array {
        return $this->dispatchCommand(new Command\StatsJob($jobId));
    }

    /**
     * @param string $tubeName
     *
     * @return array|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function statsTube(string $tubeName): ?array {
        return $this->dispatchCommand(new Command\StatsTube($tubeName));
    }

    /**
     * @param int $jobId
     *
     * @return bool
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function touch(int $jobId): bool {
        return $this->dispatchCommand(new Command\Touch($jobId));
    }

    /**
     * @param string $tube
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function useTube(string $tube): self {
        if ($tube !== $this->dispatchCommand(new Command\UseTube($tube))) {
            throw new Exception\CommandException("Tube used not matches requested tube");
        }

        return $this;
    }

    /**
     * @param string $tube
     *
     * @return \xobotyi\beansclient\BeansClient
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\ClientException
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \xobotyi\beansclient\Exception\JobException
     */
    public
    function watchTube(string $tube): self {
        $this->dispatchCommand(new Command\WatchTube($tube));

        return $this;
    }
}
