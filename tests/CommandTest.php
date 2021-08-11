<?php declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Beanstalkd;
use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exceptions\CommandException;
use xobotyi\beansclient\Serializer\JsonSerializer;

class CommandTest extends TestCase
{

  public function test__construct()
  {
    new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_OK], false, false);
    $this->addToAssertionCount(1);

    new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_OK], true, true);
    $this->addToAssertionCount(1);
  }

  public function test__constructException1()
  {
    $this->expectException(CommandException::class);
    $this->expectExceptionMessage(sprintf('Unknown beanstalkd command `%s`', 'non-existent-command'));
    new Command('non-existent-command', [Beanstalkd::STATUS_OK], false, false);
  }

  public function test__constructException2()
  {
    $this->expectException(CommandException::class);
    $this->expectExceptionMessage(sprintf('Unknown beanstalkd response status `%s`', 'unknown-status'));
    new Command(Beanstalkd::CMD_WATCH, ['unknown-status'], false, false);
  }

  public function testBuildCommand()
  {
    $cmd = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_OK], false, false);

    $this->assertEquals("watch 123 321\r\n", $cmd->buildCommand(['123', '321']));

    $this->assertEquals(
      "watch 123 321 16\r\nsome job payload\r\n",
      $cmd->buildCommand(['123', '321'], "some job payload")
    );
  }

  public function testHandleResponse()
  {
    $cmd = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_BURIED], false, false);

    $this->assertEquals(
      [
        'status' => Beanstalkd::STATUS_BURIED,
        'headers' => [
          '123',
          '321',
        ],
        'data' => null,
      ],
      $cmd->handleResponse(['status' => Beanstalkd::STATUS_BURIED, 'headers' => ['123', '321']], null),
    );

    $serializer = new JsonSerializer();
    $cmd        = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_BURIED], true, false);
    $this->assertEquals(
      [
        'status' => Beanstalkd::STATUS_BURIED,
        'headers' => [
          '123',
          '321',
        ],
        'data' => "job payload",
      ],
      $cmd->handleResponse(
        ['status' => Beanstalkd::STATUS_BURIED, 'headers' => ['123', '321'], 'data' => "\"job payload\"\r\n"],
        $serializer
      ),
    );

    $cmd = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_BURIED], false, true);
    $this->assertEquals(
      [
        'status' => Beanstalkd::STATUS_BURIED,
        'headers' => [],
        'data' => [
          'tube1',
          'tube2',
        ],
      ],
      $cmd->handleResponse(
        ['status' => Beanstalkd::STATUS_BURIED, 'headers' => [], 'data' => "----\n- tube1\n- tube2\r\n"],
        $serializer
      ),
    );
  }

  public function testHandleResponseException1()
  {
    $cmd = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_OK], false, false);

    $this->expectException(CommandException::class);
    $this->expectExceptionMessage(
      sprintf(
        'Error status `%s` received in response to `%s` command',
        Beanstalkd::STATUS_OUT_OF_MEMORY,
        Beanstalkd::CMD_WATCH
      )
    );
    $cmd->handleResponse(['status' => Beanstalkd::STATUS_OUT_OF_MEMORY], null);
  }

  public function testHandleResponseException2()
  {
    $cmd = new Command(Beanstalkd::CMD_WATCH, [Beanstalkd::STATUS_OK], false, false);

    $this->expectException(CommandException::class);
    $this->expectExceptionMessage(
      sprintf(
        'Unexpected status `%s` received in response to `%s` command',
        Beanstalkd::STATUS_DEADLINE_SOON,
        Beanstalkd::CMD_WATCH
      )
    );
    $cmd->handleResponse(['status' => Beanstalkd::STATUS_DEADLINE_SOON], null);
  }
}
