<?php declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Beanstalkd;
use xobotyi\beansclient\Commander;
use xobotyi\beansclient\Exceptions\CommandException;

class CommanderTest extends TestCase
{
  public function testGetCommand()
  {
    $cmd = Commander::getCommand(Beanstalkd::CMD_WATCH);
    $this->assertEquals($cmd, Commander::getCommand(Beanstalkd::CMD_WATCH));
  }

  public function testGetCommandException1()
  {
    $this->expectException(CommandException::class);
    $this->expectExceptionMessage(
      sprintf('Unknown beanstalkd command `%s`', 'unknown command')
    );

    $cmd = Commander::getCommand('unknown command');
  }
}
