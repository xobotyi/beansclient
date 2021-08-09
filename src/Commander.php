<?php declare(strict_types=1);


namespace xobotyi\beansclient;


use xobotyi\beansclient\Exceptions\CommandException;

class Commander
{
  private const COMMAND_CONFIG = [
    Beanstalkd::CMD_PUT => [
      'expectedStatuses' => [
        Beanstalkd::STATUS_INSERTED,
        Beanstalkd::STATUS_BURIED,
        Beanstalkd::STATUS_EXPECTED_CRLF,
        Beanstalkd::STATUS_JOB_TOO_BIG,
        Beanstalkd::STATUS_DRAINING,
      ],
    ],

    Beanstalkd::CMD_USE => [
      'expectedStatuses' => [Beanstalkd::STATUS_USING],
    ],

    Beanstalkd::CMD_RESERVE => [
      'expectedStatuses' => [
        Beanstalkd::STATUS_TIMED_OUT,
        Beanstalkd::STATUS_DEADLINE_SOON,
        Beanstalkd::STATUS_RESERVED,
      ],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_RESERVE_WITH_TIMEOUT => [
      'expectedStatuses' => [
        Beanstalkd::STATUS_TIMED_OUT,
        Beanstalkd::STATUS_DEADLINE_SOON,
        Beanstalkd::STATUS_RESERVED,
      ],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_RESERVE_JOB => [
      'expectedStatuses' => [Beanstalkd::STATUS_NOT_FOUND, Beanstalkd::STATUS_RESERVED],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_DELETE => [
      'expectedStatuses' => [Beanstalkd::STATUS_NOT_FOUND, Beanstalkd::STATUS_DELETED],
    ],
    Beanstalkd::CMD_RELEASE => [
      'expectedStatuses' => [
        Beanstalkd::STATUS_RELEASED,
        Beanstalkd::STATUS_BURIED,
        Beanstalkd::STATUS_NOT_FOUND,
      ],
    ],
    Beanstalkd::CMD_BURY => [
      'expectedStatuses' => [Beanstalkd::STATUS_BURIED, Beanstalkd::STATUS_NOT_FOUND],
    ],
    Beanstalkd::CMD_TOUCH => [
      'expectedStatuses' => [Beanstalkd::STATUS_TOUCHED, Beanstalkd::STATUS_NOT_FOUND],
    ],

    Beanstalkd::CMD_WATCH => [
      'expectedStatuses' => [Beanstalkd::STATUS_WATCHING],
    ],
    Beanstalkd::CMD_IGNORE => [
      'expectedStatuses' => [Beanstalkd::STATUS_WATCHING, Beanstalkd::STATUS_NOT_IGNORED],
    ],

    Beanstalkd::CMD_PEEK => [
      'expectedStatuses' => [Beanstalkd::STATUS_FOUND, Beanstalkd::STATUS_NOT_FOUND],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_PEEK_READY => [
      'expectedStatuses' => [Beanstalkd::STATUS_FOUND, Beanstalkd::STATUS_NOT_FOUND],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_PEEK_BURIED => [
      'expectedStatuses' => [Beanstalkd::STATUS_FOUND, Beanstalkd::STATUS_NOT_FOUND],
      'payloadBody' => true,
    ],
    Beanstalkd::CMD_PEEK_DELAYED => [
      'expectedStatuses' => [Beanstalkd::STATUS_FOUND, Beanstalkd::STATUS_NOT_FOUND],
      'payloadBody' => true,
    ],

    Beanstalkd::CMD_KICK => [
      'expectedStatuses' => [Beanstalkd::STATUS_KICKED],
    ],
    Beanstalkd::CMD_KICK_JOB => [
      'expectedStatuses' => [Beanstalkd::STATUS_KICKED, Beanstalkd::STATUS_NOT_FOUND],
    ],

    Beanstalkd::CMD_STATS => [
      'expectedStatuses' => [Beanstalkd::STATUS_OK],
      'yamlBody' => true,
    ],
    Beanstalkd::CMD_STATS_JOB => [
      'expectedStatuses' => [Beanstalkd::STATUS_OK, Beanstalkd::STATUS_NOT_FOUND],
      'yamlBody' => true,
    ],
    Beanstalkd::CMD_STATS_TUBE => [
      'expectedStatuses' => [Beanstalkd::STATUS_OK, Beanstalkd::STATUS_NOT_FOUND],
      'yamlBody' => true,
    ],

    Beanstalkd::CMD_LIST_TUBES => [
      'expectedStatuses' => [Beanstalkd::STATUS_OK],
      'yamlBody' => true,
    ],
    Beanstalkd::CMD_LIST_TUBE_USED => [
      'expectedStatuses' => [Beanstalkd::STATUS_USING],
    ],
    Beanstalkd::CMD_LIST_TUBES_WATCHED => [
      'yamlBody' => true,
      'expectedStatuses' => [Beanstalkd::STATUS_OK],
    ],

    Beanstalkd::CMD_PAUSE_TUBE => [
      'expectedStatuses' => [Beanstalkd::STATUS_PAUSED, Beanstalkd::STATUS_NOT_FOUND],
    ],

    Beanstalkd::CMD_QUIT => [
      'expectedStatuses' => [],
    ],
  ];

  /**
   * @var Command[]
   */
  private static array $commands = [];

  /**
   * @throws CommandException
   */
  public static function getCommand(string $cmd): Command
  {
    $command = self::$commands[$cmd] ?? null;

    if ($command !== null) {
      return $command;
    }

    $cfg = self::COMMAND_CONFIG[$cmd] ?? null;

    if (empty($cfg)) {
      throw new CommandException(sprintf('Unknown beanstalkd command `%s`', $cmd));
    }

    $command = new Command(
      command: $cmd,
      expectedStatuses: $cfg['expectedStatuses'] ?? [],
      payloadBody: $cfg['payloadBody'] ?? false,
      yamlBody: $cfg['yamlBody'] ?? false,
    );

    self::$commands[$cmd] = $command;

    return $command;
  }
}
