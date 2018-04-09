<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;

/**
 * Class Job
 *
 * @property-read null|int    id          job id.
 * @property-read null|mixed  payload     job's payload
 * @property-read null|string tube        name of the tube that contains this job.
 * @property-read null|string state       'deleted' or 'ready' or 'buried' or 'reserved' or 'delayed'.
 * @property-read null|number priority    priority value set by the put, release, or bury commands.
 * @property-read null|int    age         time in seconds since the put command that created this job.
 * @property-read null|int    delay       integer number of seconds to wait before putting this job in the ready queue.
 * @property-read null|int    ttr         time to run - is the integer number of seconds a worker is allowed to run
 *                this job.
 * @property-read null|int    timeLeft    number of seconds left until the server puts this job into the ready queue.
 *                This number is only meaningful if the job is reserved or delayed. If the job is reserved and this
 *                amount of time elapses before its state changes, it is considered to have timed out.
 * @property-read null|int    releaseTime UNIX timestamp representing time when job will become ready. This number is
 *                only meaningful if the job is reserved or delayed.
 * @property-read null|int    file        number of the earliest binlog file containing this job. If -b wasn't used,
 *                this will be 0.
 * @property-read null|int    reserves    number of times this job has been reserved.
 * @property-read null|int    timeouts    number of times this job has timed out during a reservation.
 * @property-read null|int    releases    number of times a client has released this job from a reservation.
 * @property-read null|int    buries      number of times this job has been buried.
 * @property-read null|int    kicks       number of times this job has been kicked.
 *
 * @package xobotyi\beansclient
 */
class Job
{
    public const STATE_DELETED  = 'deleted';
    public const STATE_READY    = 'ready';
    public const STATE_BURIED   = 'buried';
    public const STATE_RESERVED = 'reserved';
    public const STATE_DELAYED  = 'delayed';

    private const STATS_FIELDS = [
        'tube'        => 'tube',
        'state'       => 'state',
        'priority'    => 'pri',
        'age'         => 'age',
        'delay'       => 'delay',
        'ttr'         => 'ttr',
        'timeLeft'    => 'time-left',
        'releaseTime' => null,
        'file'        => 'file',
        'reserves'    => 'reserves',
        'timeouts'    => 'timeouts',
        'releases'    => 'releases',
        'buries'      => 'buries',
        'kicks'       => 'kicks',
    ];

    private const PEEK_FIELDS = [
        'payload' => 'payload',
    ];

    private $data = [
        'id'          => null,
        'payload'     => null,
        'tube'        => null,
        'state'       => null,
        'priority'    => null,
        'age'         => null,
        'delay'       => null,
        'ttr'         => null,
        'timeLeft'    => null,
        'releaseTime' => null,
        'file'        => null,
        'reserves'    => null,
        'timeouts'    => null,
        'releases'    => null,
        'buries'      => null,
        'kicks'       => null,
    ];

    /**
     * @var \xobotyi\beansclient\BeansClient;
     */
    private $client;

    /**
     * Job constructor.
     *
     * @param \xobotyi\beansclient\BeansClient $beansClient
     * @param int                              $id
     * @param string|null                      $state
     * @param null                             $payload
     *
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function __construct(BeansClient &$beansClient, int $id, string $state = null, $payload = null) {
        $this->data['id']      = $id;
        $this->data['state']   = $state;
        $this->data['payload'] = $payload;

        $this->setClient($beansClient);
    }

    /**
     * @return \xobotyi\beansclient\BeansClient
     */
    public function getClient() :BeansClient {
        return $this->client;
    }

    /**
     * @param \xobotyi\beansclient\BeansClient $beansClient
     *
     * @return $this
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function setClient(BeansClient &$beansClient) {
        if (!$beansClient->getConnection()->isActive()) {
            throw new Exception\Job("Given client has inactive connection");
        }

        $this->client = $beansClient;

        return $this;
    }

    /**
     * @return array
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function getData() :array {
        if (!$this->data['tube']) {
            $this->stats();
        }
        if (!$this->data['payload']) {
            $this->peek();
        }

        return $this->data;
    }

    /**
     * @param $offset
     *
     * @return mixed|null
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function __get($offset) {
        if (!\array_key_exists($offset, $this->data)) {
            trigger_error("Undefined property: " . self::class . "::\${$offset}");

            return null;
        }

        if (!$this->data['state'] || ($this->data[$offset] === null && $this->data['state'] !== self::STATE_DELETED)) {
            if (\array_key_exists($offset, self::STATS_FIELDS)) {
                $this->stats();
            }
            else if (\array_key_exists($offset, self::PEEK_FIELDS)) {
                $this->peek();
            }
        }

        if ($this->data['state'] === self::STATE_DELAYED || $this->data['state'] === self::STATE_RESERVED) {
            $this->data['timeLeft'] = $this->data['releaseTime'] - time();

            if ($this->data['timeLeft'] <= 0) {
                $this->stats();
            }
        }

        return $this->data[$offset];
    }

    /**
     * @return bool
     */
    public function isDeleted() :bool {
        return $this->data['state'] === self::STATE_DELETED;
    }

    /**
     * @return bool
     */
    public function isDelayed() :bool {
        return $this->data['state'] === self::STATE_DELAYED;
    }

    /**
     * @return bool
     */
    public function isReady() :bool {
        return $this->data['state'] === self::STATE_READY;
    }

    /**
     * @return bool
     */
    public function isReserved() :bool {
        return $this->data['state'] === self::STATE_RESERVED;
    }

    /**
     * @return bool
     */
    public function isBuried() :bool {
        return $this->data['state'] === self::STATE_BURIED;
    }

    /**
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function stats() :self {
        if ($stats = $this->client->statsJob($this->data['id'])) {
            foreach (self::STATS_FIELDS as $tgt => $src) {
                if ($src === null) {
                    switch ($tgt) {
                        case 'releaseTime':
                            $this->data['releaseTime'] = ($this->data['state'] === self::STATE_DELAYED || $this->data['state'] === self::STATE_RESERVED)
                                ? time() + $this->data['timeLeft'] ?? 0
                                : 0;
                            break;
                    }
                }
                else if (!\array_key_exists($src, $stats)) {
                    continue;
                }
                else if (\is_numeric($this->data[$tgt] = $stats[$src])) {
                    $this->data[$tgt] *= 1;
                }
            }
        }
        else {
            $this->clearStats();

            foreach (self::PEEK_FIELDS as $tgt => $src) {
                $this->data[$tgt] = null;
            }

            $this->data['state'] = self::STATE_DELETED;
        }

        return $this;
    }

    // commands

    /**
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function peek() :self {
        $job = $this->client->peek($this->data['id']);

        foreach (self::PEEK_FIELDS as $tgt => $src) {
            $this->data[$tgt] = $job[$src];
        }

        return $this;
    }

    /**
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function touch() :self {
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

    /**
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function kick() :self {
        $this->client->kickJob($this->data['id']);

        return $this->stats();
    }

    /**
     * @param int $priority
     *
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function bury(int $priority = BeansClient::DEFAULT_PRIORITY) :self {
        if ($this->client->bury($this->data['id'], $priority)) {
            $this->data['state']       = self::STATE_BURIED;
            $this->data['priority']    = $priority;
            $this->data['delay']       = 0;
            $this->data['timeLeft']    = 0;
            $this->data['releaseTime'] = 0;
        }
        else {
            $this->clearStats();
        }

        return $this;
    }

    /**
     * @return \xobotyi\beansclient\Job
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function delete() :self {
        $this->clearStats();

        if ($this->client->delete($this->data['id'])) {
            foreach (self::PEEK_FIELDS as $tgt => $src) {
                $this->data[$tgt] = null;
            }

            $this->data['state'] = self::STATE_DELETED;
        }

        return $this;
    }

    /**
     * @param null     $priority
     * @param int|null $delay
     *
     * @return $this
     * @throws \xobotyi\beansclient\Exception\Client
     * @throws \xobotyi\beansclient\Exception\Command
     * @throws \xobotyi\beansclient\Exception\Job
     */
    public function release($priority = null, int $delay = null) {
        $priority = $priority === null ? $this->client::DEFAULT_PRIORITY : $priority;
        $delay    = $delay === null ? $this->client::DEFAULT_DELAY : $delay;

        if ($this->client->release($this->data['id'], $priority, $delay) === Response::RELEASED) {
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
        else {
            $this->clearStats();
        }

        return $this;
    }

    /**
     * @return \xobotyi\beansclient\Job
     */
    private function clearStats() :self {
        foreach (self::STATS_FIELDS as $field) {
            $this->data[$field] = null;
        }

        return $this;
    }
}