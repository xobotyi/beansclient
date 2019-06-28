<?php
declare(strict_types=1);

namespace xobotyi\beansclient;

use xobotyi\beansclient\Exception\JobException;

class Job
{
    public const STATE_DELETED  = "deleted";
    public const STATE_READY    = "ready";
    public const STATE_RESERVED = "reserved";
    public const STATE_DELAYED  = "delayed";
    public const STATE_BURIED   = "buried";

    private const STATS_COMMAND_FIELDS = [
        "tube"        => "tube",
        "state"       => "state",
        "priority"    => "pri",
        "age"         => "age",
        "delay"       => "delay",
        "ttr"         => "ttr",
        "timeLeft"    => "time-left",
        "releaseTime" => false,
        "file"        => "file",
        "reserves"    => "reserves",
        "timeouts"    => "timeouts",
        "releases"    => "releases",
        "buries"      => "buries",
        "kicks"       => "kicks",
    ];

    private const PEEK_COMMAND_FIELDS = [
        "payload" => "payload",
    ];

    private $data = [
        "id"          => null,
        "payload"     => null,
        "tube"        => null,
        "state"       => null,
        "priority"    => null,
        "age"         => null,
        "delay"       => null,
        "ttr"         => null,
        "timeLeft"    => null,
        "releaseTime" => null,
        "file"        => null,
        "reserves"    => null,
        "timeouts"    => null,
        "releases"    => null,
        "buries"      => null,
        "kicks"       => null,
    ];

    /**
     * @var \xobotyi\beansclient\BeansClient
     */
    private $client;

    public
    function __construct(BeansClient $client, ?int $id = null, ?string $state = null, $payload = null) {
        $this->setClient($client);

        $this->data['id']      = $id;
        $this->data['state']   = $state;
        $this->data['payload'] = $payload;
    }

    public
    function __get($offset) {
        if (!isset($this->data[$offset]) && !array_key_exists($offset, $this->data)) {
            trigger_error(sprintf("Undefined property: %s::%s", self::class, $offset));

            return null;
        }

        if (!$this->data['id']) {
            return null;
        }

        if (!$this->data['state'] || ($this->data[$offset] === null && $this->data['state'] !== self::STATE_DELETED)) {
            if ((self::STATS_COMMAND_FIELDS[$offset] ?? null) !== null) {
                $this->stats();
            }
            else if ((self::PEEK_COMMAND_FIELDS[$offset] ?? null) !== null) {
                $this->peek();
            }
        }

        if (($this->data['releaseTime'] ?? null) && ($this->data['state'] === self::STATE_DELAYED || $this->data['state'] === self::STATE_RESERVED)) {
            $this->data['timeLeft'] = $this->data['releaseTime'] - time();

            if ($this->data['timeLeft'] <= 0) {
                $this->stats();
            }
        }

        return $this->data[$offset];
    }

    public
    function setClient(BeansClient $client): self {
        if (!$client->getConnection()->isActive()) {
            throw new JobException("Given client has inactive connection");
        }

        $this->client = $client;

        return $this;
    }

    public
    function getClient(): ?BeansClient {
        return $this->client;
    }

    public
    function isDeleted(): bool {
        return $this->data['state'] === self::STATE_DELETED;
    }

    public
    function isReady(): bool {
        return $this->data['state'] === self::STATE_READY;
    }

    public
    function isReserved(): bool {
        return $this->data['state'] === self::STATE_RESERVED;
    }

    public
    function isDelayed(): bool {
        return $this->data['state'] === self::STATE_DELAYED;
    }

    public
    function isBuried(): bool {
        return $this->data['state'] === self::STATE_BURIED;
    }

    public
    function getAllData(): array {
        if (!$this->data['id']) {
            return $this->data;
        }

        if (!$this->data['payload']) {
            $this->peek();
        }

        if (!$this->data['tube']) {
            $this->stats();
        }

        return $this->data;
    }

    private
    function clearStatsFields(): self {
        foreach (self::STATS_COMMAND_FIELDS as $field => $src) {
            $this->data[$field] = null;
        }

        return $this;
    }

    public
    function peek(): self {
        if (!$this->data['id']) {
            return $this;
        }

        $job = $this->client->peek($this->data['id']);

        foreach (self::PEEK_COMMAND_FIELDS as $targetField => $sourceField) {
            if ($sourceField === 'payload') {
                $serializer               = $this->client->getSerializer();
                $this->data[$targetField] = (is_string($job[$sourceField]) && $serializer)
                    ? $serializer->unserialize($job[$sourceField])
                    : $job[$sourceField];

                continue;
            }

//            $this->data[$targetField] = $job[$sourceField] ?? null; // not needed yet
        }

        return $this;
    }

    public
    function bury(int $priority = null): self {
        if (!$this->data['id']) {
            return $this;
        }

        $result = $this->client->bury($this->data['id'], $priority === null ? $this->client::DEFAULT_PRIORITY : $priority);

        if ($result) {
            $this->data['state']       = self::STATE_BURIED;
            $this->data['priority']    = $priority;
            $this->data['delay']       = 0;
            $this->data['timeLeft']    = 0;
            $this->data['releaseTime'] = 0;
        }
        else {
            $this->stats();
        }

        return $this;
    }

    public
    function delete(): self {
        if (!$this->data['id']) {
            return $this;
        }

        $this->clearStatsFields();
        $this->data['state'] = self::STATE_DELETED;

        return $this;
    }

    public
    function kick(): self {
        if (!$this->data['id']) {
            return $this;
        }

        $this->client->kickJob($this->data['id']);

        return $this->stats();
    }

    public
    function release($priority = null, int $delay = null): self {
        if (!$this->data['id']) {
            return $this;
        }

        $delay = $delay === null ? $this->client::DEFAULT_DELAY : $delay;

        $result = $this->client->release($this->data['id'],
                                         $priority,
                                         $delay);

        if (!$result) {
            $this->clearStatsFields();
            $this->data['state'] = self::STATE_DELETED;

            return $this;
        }

        if ($result === Response::BURIED) {
            $this->data['state']       = self::STATE_BURIED;
            $this->data['priority']    = $priority;
            $this->data['delay']       = 0;
            $this->data['timeLeft']    = 0;
            $this->data['releaseTime'] = 0;
        }
        else if ($result === Response::RELEASED) {
            if ($delay) {
                $this->data['state']       = self::STATE_DELAYED;
                $this->data['delay']       = $delay;
                $this->data['timeLeft']    = $delay;
                $this->data['releaseTime'] = time() + $delay;
            }
            else {
                $this->data['state']       = self::STATE_READY;
                $this->data['delay']       = 0;
                $this->data['releaseTime'] = 0;
                $this->data['timeLeft']    = 0;
            }
        }

        return $this;
    }

    public
    function touch(): self {
        if (!$this->data['id']) {
            return $this;
        }

        if ($this->client->touch($this->data['id'])) {
            $this->data['delay']       = 0;
            $this->data['timeLeft']    = $this->data['ttr'];
            $this->data['releaseTime'] = time() + $this->data['ttr'];
        }
        else {
            $this->stats();
        }

        return $this;
    }

    public
    function stats(): self {
        if (!$this->data['id']) {
            return $this;
        }

        if ($jobStats = $this->client->statsJob($this->data['id'])) {
            foreach (self::STATS_COMMAND_FIELDS as $targetField => $sourceField) {
                if ($sourceField === false) {
                    if ($targetField === 'releaseTime') {
                        $this->data['releaseTime'] = ($this->data['state'] === self::STATE_DELAYED || $this->data['state'] === self::STATE_RESERVED)
                            ? time() + $jobStats['time-left'] ?? 0
                            : 0;
                    }

                    continue;
                }

                if (!isset($jobStats[$sourceField]) && !array_key_exists($sourceField, $jobStats)) {
                    continue;
                }

                if (is_numeric($this->data[$targetField] = $jobStats[$sourceField])) {
                    $this->data[$targetField] *= 1;
                }
            }
        }
        else {
            $this->clearStatsFields();
            $this->data['state'] = self::STATE_DELETED;
        }

        return $this;
    }
}