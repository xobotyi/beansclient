<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient;


/**
 * Class Response
 *
 * @package xobotyi\beansclient
 */
class Response
{
    public const OUT_OF_MEMORY   = 'OUT_OF_MEMORY';
    public const INTERNAL_ERROR  = 'INTERNAL_ERROR';
    public const BAD_FORMAT      = 'BAD_FORMAT';
    public const UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';
    public const INSERTED        = 'INSERTED';
    public const BURIED          = 'BURIED';
    public const EXPECTED_CRLF   = 'EXPECTED_CRLF';
    public const JOB_TOO_BIG     = 'JOB_TOO_BIG';
    public const DRAINING        = 'DRAINING';
    public const USING           = 'USING';
    public const DEADLINE_SOON   = 'DEADLINE_SOON';
    public const TIMED_OUT       = 'TIMED_OUT';
    public const RESERVED        = 'RESERVED';
    public const DELETED         = 'DELETED';
    public const NOT_FOUND       = 'NOT_FOUND';
    public const RELEASED        = 'RELEASED';
    public const WATCHING        = 'WATCHING';
    public const NOT_IGNORED     = 'NOT_IGNORED';
    public const FOUND           = 'FOUND';
    public const KICKED          = 'KICKED';
    public const OK              = 'OK';
    public const PAUSED          = 'PAUSED';
    public const TOUCHED         = 'TOUCHED';

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

    //ToDo: replace it with any normal YAML parser

    /**
     * @param string $str
     * @param bool   $assoc
     *
     * @return array|null
     * @throws \Exception
     */
    public static function YamlParse(string $str, bool $assoc = false) :?array {
        if (!($str = trim($str))) {
            return null;
        }

        $result = [];
        $lines  = preg_split("/[\r\n]+/", $str);

        foreach ($lines as $line) {
            if (!$line || $line === '---') {
                continue;
            }

            if (substr($line, 0, 2) === '- ') {
                $result[] = substr($line, 2);
            }
            else if ($assoc) {
                if (!preg_match('/([\S]+)\:[\s]*(.*)/', $line, $res)) {
                    throw new \Exception('Failed to parse YAML string [' . $line . ']');
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