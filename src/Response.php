<?php

namespace xobotyi\beansclient;


use Exception;

/**
 * Class Response
 *
 * @package xobotyi\beansclient
 */
class Response
{
    public const BAD_FORMAT      = 'BAD_FORMAT';
    public const BURIED          = 'BURIED';
    public const DEADLINE_SOON   = 'DEADLINE_SOON';
    public const DELETED         = 'DELETED';
    public const DRAINING        = 'DRAINING';
    public const EXPECTED_CRLF   = 'EXPECTED_CRLF';
    public const FOUND           = 'FOUND';
    public const INSERTED        = 'INSERTED';
    public const INTERNAL_ERROR  = 'INTERNAL_ERROR';
    public const JOB_TOO_BIG     = 'JOB_TOO_BIG';
    public const KICKED          = 'KICKED';
    public const NOT_FOUND       = 'NOT_FOUND';
    public const NOT_IGNORED     = 'NOT_IGNORED';
    public const OK              = 'OK';
    public const OUT_OF_MEMORY   = 'OUT_OF_MEMORY';
    public const PAUSED          = 'PAUSED';
    public const RELEASED        = 'RELEASED';
    public const RESERVED        = 'RESERVED';
    public const TIMED_OUT       = 'TIMED_OUT';
    public const TOUCHED         = 'TOUCHED';
    public const UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';
    public const USING           = 'USING';
    public const WATCHING        = 'WATCHING';

    // list of error responses
    public const ERROR_RESPONSES = [
        self::OUT_OF_MEMORY,
        self::INTERNAL_ERROR,
        self::BAD_FORMAT,
        self::DRAINING,
        self::UNKNOWN_COMMAND,
    ];

    // list of responses followed by data
    public const DATA_RESPONSES = [
        self::OK,
        self::RESERVED,
        self::FOUND,
    ];

    /**
     * @param string $str
     * @param bool   $assoc
     *
     * @return array|null
     * @throws \Exception
     */
    public static
    function YamlParse(string $str, bool $assoc = false): ?array {
        if (!($str = trim($str))) {
            return null;
        }

        $result = [];
        $lines  = explode("\r\n", $str);

        foreach ($lines as $line) {
            if (!$line || $line === '---') {
                continue;
            }

            if (substr($line, 0, 2) === '- ') {
                $result[] = substr($line, 2);
            }
            else if ($assoc) {
                if (!preg_match('/([\S]+)\:[\s]*(.*)/', $line, $res)) {
                    throw new Exception('Failed to parse YAML string [' . $line . ']');
                }

                $result[$res[1]] = $res[2];
            }
            else {
                $result[] = $line;
            }
        }

        return $result;
    }
}

