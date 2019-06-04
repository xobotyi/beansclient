<?php


namespace xobotyi\beansclient\Interfaces;


/**
 * Interface Command
 *
 * @package xobotyi\beansclient\Interfaces
 */
interface CommandInterface
{
    public const PUT                  = 'put';
    public const USE                  = 'use';
    public const RESERVE              = 'reserve';
    public const RESERVE_WITH_TIMEOUT = 'reserve-with-timeout';
    public const DELETE               = 'delete';
    public const RELEASE              = 'release';
    public const BURY                 = 'bury';
    public const TOUCH                = 'touch';
    public const WATCH                = 'watch';
    public const IGNORE               = 'ignore';
    public const PEEK                 = 'peek';
    public const PEEK_READY           = 'peek-ready';
    public const PEEK_DELAYED         = 'peek-delayed';
    public const PEEK_BURIED          = 'peek-buried';
    public const KICK                 = 'kick';
    public const KICK_JOB             = 'kick-job';
    public const STATS                = 'stats';
    public const STATS_JOB            = 'stats-job';
    public const STATS_TUBE           = 'stats-tube';
    public const LIST_TUBES           = 'list-tubes';
    public const LIST_TUBE_USED       = 'list-tube-used';
    public const LIST_TUBES_WATCHED   = 'list-tubes-watched';
    public const PAUSE_TUBE           = 'pause-tube';
    public const QUIT                 = 'quit';

    public const COMMANDS_LIST = [
        self::PUT,
        self::USE,
        self::RESERVE,
        self::RESERVE_WITH_TIMEOUT,
        self::DELETE,
        self::RELEASE,
        self::BURY,
        self::TOUCH,
        self::WATCH,
        self::IGNORE,
        self::PEEK,
        self::PEEK_READY,
        self::PEEK_BURIED,
        self::KICK,
        self::KICK_JOB,
        self::STATS,
        self::STATS_JOB,
        self::STATS_TUBE,
        self::LIST_TUBES,
        self::LIST_TUBE_USED,
        self::LIST_TUBES_WATCHED,
        self::PAUSE_TUBE,
        self::QUIT,
    ];

    /**
     * @return string
     */
    public
    function getCommandStr(): string;

    /**
     * @return mixed
     */
    public
    function getPayload();

    /**
     * @return bool
     */
    public
    function hasPayload(): bool;

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return mixed
     */
    public
    function parseResponse(array $responseHeader, ?string $responseStr);

    /**
     * @param \xobotyi\beansclient\Interfaces\SerializerInterface $serialize
     *
     * @return mixed
     */
    public
    function setSerializer(SerializerInterface $serialize);
}