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
        "pri"         => "pri",
        "age"         => "age",
        "delay"       => "delay",
        "ttr"         => "ttr",
        "timeLeft"    => "time-left",
        "releaseTime" => null,
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

    private $data = [];

    /**
     * @var \xobotyi\beansclient\BeansClientOld
     */
    private $client;

    public
    function __construct(BeansClient $client, ?int $id = null, ?string $status = null, $payload = null) {
        $this->setClient($client);

        $this->data['id']      = $id;
        $this->data['status']  = $status;
        $this->data['payload'] = $payload;
    }

    public
    function setClient(BeansClientOld $client): self {
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
    function clearData(): self {
        $data = [];

        if ($this->data['id']) {
            $data['id'] = $this->data['id'];
        }
        if ($this->data['state']) {
            $data['state'] = $this->data['state'];
        }
        if ($this->data['payload']) {
            $data['payload'] = $this->data['payload'];
        }

        $this->data = $data;

        return $this;
    }

    public
    function peek(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function bury(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function delete(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function kick(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function release(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function touch(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }

    public
    function stats(): self {
        if (!$this->data['id']) {
            return $this;
        }

        return $this;
    }
}