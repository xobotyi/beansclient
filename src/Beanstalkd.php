<?php declare(strict_types=1);


namespace xobotyi\beansclient;


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use xobotyi\beansclient\Exceptions\ResponseException;

final class Beanstalkd
{
  public const CRLF     = "\r\n";
  public const CRLF_LEN = 2;

  public const PRIORITY_MIN = 0;
  public const PRIORITY_MAX = 4_294_967_295;

  public const DELAY_MIN = 0;
  public const DELAY_MAX = 4_294_967_295;

  public const TTR_MIN = 0;
  public const TTR_MAX = 4_294_967_295;

  public const TIMEOUT_MIN = 0;

  public const JOB_ID_MIN = 1;

  public static function validatePriority(int $priority)
  {
    if ($priority < Beanstalkd::PRIORITY_MIN) {
      throw new \InvalidArgumentException("priority should be >= " . Beanstalkd::PRIORITY_MIN);
    }
    if ($priority > Beanstalkd::PRIORITY_MAX) {
      throw new \InvalidArgumentException("priority should be <= " . Beanstalkd::PRIORITY_MAX);
    }
  }

  public static function validateDelay(int $delay)
  {
    if ($delay < Beanstalkd::DELAY_MIN) {
      throw new \InvalidArgumentException("delay should be >= " . Beanstalkd::DELAY_MIN);
    }
    if ($delay > Beanstalkd::DELAY_MAX) {
      throw new \InvalidArgumentException("delay should be <= " . Beanstalkd::DELAY_MAX);
    }
  }

  public static function validateTTR(int $ttr)
  {
    if ($ttr < Beanstalkd::TTR_MIN) {
      throw new \InvalidArgumentException("ttr should be >= " . Beanstalkd::TTR_MIN);
    }
    if ($ttr > Beanstalkd::TTR_MAX) {
      throw new \InvalidArgumentException("ttr should be <= " . Beanstalkd::TTR_MAX);
    }
  }

  public static function validateTimeout(int $timeout)
  {
    if ($timeout < Beanstalkd::TIMEOUT_MIN) {
      throw new \InvalidArgumentException("timeout should be >= " . Beanstalkd::TIMEOUT_MIN);
    }
  }

  public static function validateJobID(int $jobId)
  {
    if ($jobId < Beanstalkd::JOB_ID_MIN) {
      throw new \InvalidArgumentException("job id should be >= " . Beanstalkd::JOB_ID_MIN);
    }
  }

  public static function validateTubeName(string $name)
  {
    if (!preg_match('~^[A-Za-z0-9\-+/;.$_()]{1,200}$~', $name)) {
      throw new \InvalidArgumentException('tube name should satisfy regexp: /^[A-Za-z0-9-+/;.$_()]{1,200}$/');
    }
  }

  public const CMD_PUT                  = 'put';
  public const CMD_USE                  = 'use';
  public const CMD_RESERVE              = 'reserve';
  public const CMD_RESERVE_WITH_TIMEOUT = 'reserve-with-timeout';
  public const CMD_RESERVE_JOB          = 'reserve-job';
  public const CMD_DELETE               = 'delete';
  public const CMD_RELEASE              = 'release';
  public const CMD_BURY                 = 'bury';
  public const CMD_TOUCH                = 'touch';
  public const CMD_WATCH                = 'watch';
  public const CMD_IGNORE               = 'ignore';
  public const CMD_PEEK                 = 'peek';
  public const CMD_PEEK_READY           = 'peek-ready';
  public const CMD_PEEK_DELAYED         = 'peek-delayed';
  public const CMD_PEEK_BURIED          = 'peek-buried';
  public const CMD_KICK                 = 'kick';
  public const CMD_KICK_JOB             = 'kick-job';
  public const CMD_STATS                = 'stats';
  public const CMD_STATS_JOB            = 'stats-job';
  public const CMD_STATS_TUBE           = 'stats-tube';
  public const CMD_LIST_TUBES           = 'list-tubes';
  public const CMD_LIST_TUBE_USED       = 'list-tube-used';
  public const CMD_LIST_TUBES_WATCHED   = 'list-tubes-watched';
  public const CMD_PAUSE_TUBE           = 'pause-tube';
  public const CMD_QUIT                 = 'quit';

  const CMDS_LIST = [
    self::CMD_PUT => true,
    self::CMD_USE => true,
    self::CMD_RESERVE => true,
    self::CMD_RESERVE_WITH_TIMEOUT => true,
    self::CMD_RESERVE_JOB => true,
    self::CMD_DELETE => true,
    self::CMD_RELEASE => true,
    self::CMD_BURY => true,
    self::CMD_TOUCH => true,
    self::CMD_WATCH => true,
    self::CMD_IGNORE => true,
    self::CMD_PEEK => true,
    self::CMD_PEEK_READY => true,
    self::CMD_PEEK_DELAYED => true,
    self::CMD_PEEK_BURIED => true,
    self::CMD_KICK => true,
    self::CMD_KICK_JOB => true,
    self::CMD_STATS => true,
    self::CMD_STATS_JOB => true,
    self::CMD_STATS_TUBE => true,
    self::CMD_LIST_TUBES => true,
    self::CMD_LIST_TUBE_USED => true,
    self::CMD_LIST_TUBES_WATCHED => true,
    self::CMD_PAUSE_TUBE => true,
    self::CMD_QUIT => true,
  ];

  public static function supportsCommand(string $command): bool
  {
    return self::CMDS_LIST[$command] ?? false;
  }

  public const STATUS_BAD_FORMAT      = 'BAD_FORMAT';
  public const STATUS_BURIED          = 'BURIED';
  public const STATUS_DEADLINE_SOON   = 'DEADLINE_SOON';
  public const STATUS_DELETED         = 'DELETED';
  public const STATUS_DRAINING        = 'DRAINING';
  public const STATUS_EXPECTED_CRLF   = 'EXPECTED_CRLF';
  public const STATUS_FOUND           = 'FOUND';
  public const STATUS_INSERTED        = 'INSERTED';
  public const STATUS_INTERNAL_ERROR  = 'INTERNAL_ERROR';
  public const STATUS_JOB_TOO_BIG     = 'JOB_TOO_BIG';
  public const STATUS_KICKED          = 'KICKED';
  public const STATUS_NOT_FOUND       = 'NOT_FOUND';
  public const STATUS_NOT_IGNORED     = 'NOT_IGNORED';
  public const STATUS_OK              = 'OK';
  public const STATUS_OUT_OF_MEMORY   = 'OUT_OF_MEMORY';
  public const STATUS_PAUSED          = 'PAUSED';
  public const STATUS_RELEASED        = 'RELEASED';
  public const STATUS_RESERVED        = 'RESERVED';
  public const STATUS_TIMED_OUT       = 'TIMED_OUT';
  public const STATUS_TOUCHED         = 'TOUCHED';
  public const STATUS_UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';
  public const STATUS_USING           = 'USING';
  public const STATUS_WATCHING        = 'WATCHING';

  const STATUSES_LIST = [
    self::STATUS_BAD_FORMAT => true,
    self::STATUS_BURIED => true,
    self::STATUS_DEADLINE_SOON => true,
    self::STATUS_DELETED => true,
    self::STATUS_DRAINING => true,
    self::STATUS_EXPECTED_CRLF => true,
    self::STATUS_FOUND => true,
    self::STATUS_INSERTED => true,
    self::STATUS_INTERNAL_ERROR => true,
    self::STATUS_JOB_TOO_BIG => true,
    self::STATUS_KICKED => true,
    self::STATUS_NOT_FOUND => true,
    self::STATUS_NOT_IGNORED => true,
    self::STATUS_OK => true,
    self::STATUS_OUT_OF_MEMORY => true,
    self::STATUS_PAUSED => true,
    self::STATUS_RELEASED => true,
    self::STATUS_RESERVED => true,
    self::STATUS_TIMED_OUT => true,
    self::STATUS_TOUCHED => true,
    self::STATUS_UNKNOWN_COMMAND => true,
    self::STATUS_USING => true,
    self::STATUS_WATCHING => true,
  ];

  public static function supportsResponseStatus(string $status): bool
  {
    return self::STATUSES_LIST[$status] ?? false;
  }

  const DATA_STATUSES_LIST = [
    self::STATUS_OK => true,
    self::STATUS_RESERVED => true,
    self::STATUS_FOUND => true,
  ];

  public static function isDataResponseStatus(string $status): bool
  {
    return self::DATA_STATUSES_LIST[$status] ?? false;
  }

  const ERROR_STATUSES_LIST = [
    self::STATUS_OUT_OF_MEMORY => true,
    self::STATUS_INTERNAL_ERROR => true,
    self::STATUS_BAD_FORMAT => true,
    self::STATUS_DRAINING => true,
    self::STATUS_UNKNOWN_COMMAND => true,
  ];

  public static function isErrorStatus(string $status): bool
  {
    return self::ERROR_STATUSES_LIST[$status] ?? false;
  }

  public const JOB_STATE_READY    = 'ready';
  public const JOB_STATE_DELAYED  = 'delayed';
  public const JOB_STATE_RESERVED = 'reserved';
  public const JOB_STATE_BURIED   = 'buried';


  /**
   * @throws ResponseException
   */
  #[ArrayShape(["status" => "string", "headers" => "string[]", "hasData" => "bool", "dataLength" => "int"])]
  public static function parseResponseHeaders(string $headers): array
  {
    $headersArray = explode(" ", $headers);

    $status = (string)array_shift($headersArray);

    $hasData    = self::isDataResponseStatus($status);
    $dataLength = 0;

    if ($hasData) {
      $dataLength = array_pop($headersArray);

      if (!is_numeric($dataLength)) {
        throw new ResponseException(
          sprintf('Received invalid data length for `%s` response, expected number, got `%s`', $status, $dataLength)
        );
      }
    }

    return [
      "status" => $status,
      "headers" => $headersArray,
      "hasData" => $hasData,
      "dataLength" => $dataLength,
    ];
  }

  /**
   * @throws ResponseException
   */
  public static function simpleYamlParse(string $str): ?array
  {
    $str = rtrim($str);
    if (empty($str)) {
      return null;
    }

    $result = [];
    $lines  = explode("\n", $str);

    # 1st line is a separator - don't need it;
    array_shift($lines);

    # list array
    if (mb_substr($lines[0], 0, 2, encoding: '8BIT') === '- ') {
      foreach ($lines as $line) {
        $result[] = mb_substr($line, 2, encoding: '8BIT');
      }
    } else {
      foreach ($lines as $line) {
        if (preg_match('/([\S]+): (.+)/', $line, $res) !== 1) {
          throw new ResponseException('Failed to parse YAML string [' . $line . ']');
        }

        $result[$res[1]] = $res[2];
      }
    }

    return $result;
  }

  #[Pure]
  public static function processStats(array $arr): array
  {
    return [
      'current-jobs-urgent' => (int)$arr['current-jobs-urgent'],
      'current-jobs-ready' => (int)$arr['current-jobs-ready'],
      'current-jobs-reserved' => (int)$arr['current-jobs-reserved'],
      'current-jobs-delayed' => (int)$arr['current-jobs-delayed'],
      'current-jobs-buried' => (int)$arr['current-jobs-buried'],
      'cmd-put' => (int)$arr['cmd-put'],
      'cmd-peek' => (int)$arr['cmd-peek'],
      'cmd-peek-ready' => (int)$arr['cmd-peek-ready'],
      'cmd-peek-delayed' => (int)$arr['cmd-peek-delayed'],
      'cmd-peek-buried' => (int)$arr['cmd-peek-buried'],
      'cmd-reserve' => (int)$arr['cmd-reserve'],
      'cmd-reserve-with-timeout' => (int)$arr['cmd-reserve-with-timeout'],
      'cmd-use' => (int)$arr['cmd-use'],
      'cmd-watch' => (int)$arr['cmd-watch'],
      'cmd-ignore' => (int)$arr['cmd-ignore'],
      'cmd-delete' => (int)$arr['cmd-delete'],
      'cmd-release' => (int)$arr['cmd-release'],
      'cmd-bury' => (int)$arr['cmd-bury'],
      'cmd-kick' => (int)$arr['cmd-kick'],
      'cmd-stats' => (int)$arr['cmd-stats'],
      'cmd-stats-job' => (int)$arr['cmd-stats-job'],
      'cmd-stats-tube' => (int)$arr['cmd-stats-tube'],
      'cmd-list-tubes' => (int)$arr['cmd-list-tubes'],
      'cmd-list-tube-used' => (int)$arr['cmd-list-tube-used'],
      'cmd-list-tubes-watched' => (int)$arr['cmd-list-tubes-watched'],
      'cmd-pause-tube' => (int)$arr['cmd-pause-tube'],
      'job-timeouts' => (int)$arr['job-timeouts'],
      'cmd-touch' => (int)$arr['cmd-touch'],
      'total-jobs' => (int)$arr['total-jobs'],
      'max-job-size' => (int)$arr['max-job-size'],
      'current-tubes' => (int)$arr['current-tubes'],
      'current-connections' => (int)$arr['current-connections'],
      'current-producers' => (int)$arr['current-producers'],
      'current-workers' => (int)$arr['current-workers'],
      'current-waiting' => (int)$arr['current-waiting'],
      'total-connections' => (int)$arr['total-connections'],
      'pid' => (int)$arr['pid'],
      'version' => (string)$arr['version'],
      'rusage-utime' => (float)$arr['rusage-utime'],
      'rusage-stime' => (float)$arr['rusage-stime'],
      'uptime' => (int)$arr['uptime'],
      'binlog-oldest-index' => (int)$arr['binlog-oldest-index'],
      'binlog-current-index' => (int)$arr['binlog-current-index'],
      'binlog-max-size' => (int)$arr['binlog-max-size'],
      'binlog-records-written' => (int)$arr['binlog-records-written'],
      'binlog-records-migrated' => (int)$arr['binlog-records-migrated'],
      'draining' => (string)$arr['draining'] === 'true',
      'id' => (string)$arr['id'],
      'hostname' => (string)$arr['hostname'],
      'os' => (string)$arr['os'] ?? null,
      'platform' => (string)$arr['platform'],
    ];
  }

  #[Pure]
  public static function processTubeStats(array $arr): array
  {
    return [
      'name' => (string)$arr['name'],
      'current-jobs-urgent' => (int)$arr['current-jobs-urgent'],
      'current-jobs-ready' => (int)$arr['current-jobs-ready'],
      'current-jobs-reserved' => (int)$arr['current-jobs-reserved'],
      'current-jobs-delayed' => (int)$arr['current-jobs-delayed'],
      'current-jobs-buried' => (int)$arr['current-jobs-buried'],
      'total-jobs' => (int)$arr['total-jobs'],
      'current-using' => (int)$arr['current-using'],
      'current-waiting' => (int)$arr['current-waiting'],
      'current-watching' => (int)$arr['current-watching'],
      'pause' => (int)$arr['pause'],
      'cmd-delete' => (int)$arr['cmd-delete'],
      'cmd-pause-tube' => (int)$arr['cmd-pause-tube'],
      'pause-time-left' => (int)$arr['pause-time-left'],
    ];
  }

  #[Pure]
  public static function processJobStats(array $arr): array
  {
    return [
      'id' => (int)$arr['id'],
      'tube' => (string)$arr['tube'],
      'state' => (string)$arr['state'],
      'pri' => (int)$arr['pri'],
      'age' => (int)$arr['age'],
      'delay' => (int)$arr['delay'],
      'ttr' => (int)$arr['ttr'],
      'time-left' => (int)$arr['time-left'],
      'file' => (int)$arr['file'],
      'reserves' => (int)$arr['reserves'],
      'timeouts' => (int)$arr['timeouts'],
      'releases' => (int)$arr['releases'],
      'buries' => (int)$arr['buries'],
      'kicks' => (int)$arr['kicks'],
    ];
  }
}
