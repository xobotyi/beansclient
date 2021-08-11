<?php declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Beanstalkd;
use xobotyi\beansclient\Exceptions\ResponseException;

class BeanstalkdTest extends TestCase
{
  public function testValidateDelay()
  {
    Beanstalkd::validateDelay(Beanstalkd::DELAY_MIN);
    $this->addToAssertionCount(1);

    Beanstalkd::validateDelay(Beanstalkd::DELAY_MIN + 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateDelay(Beanstalkd::DELAY_MAX);
    $this->addToAssertionCount(1);

    Beanstalkd::validateDelay(Beanstalkd::DELAY_MAX - 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateDelay(100500);
    $this->addToAssertionCount(1);
  }

  public function testValidateDelayException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("delay should be >= " . Beanstalkd::DELAY_MIN);
    Beanstalkd::validateDelay(Beanstalkd::DELAY_MIN - 1);
  }

  public function testValidateDelayException2()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("delay should be <= " . Beanstalkd::DELAY_MAX);
    Beanstalkd::validateDelay(Beanstalkd::DELAY_MAX + 1);
  }

  public function testValidateTTR()
  {
    Beanstalkd::validateTTR(Beanstalkd::TTR_MIN);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTTR(Beanstalkd::TTR_MIN + 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTTR(Beanstalkd::TTR_MAX);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTTR(Beanstalkd::TTR_MAX - 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTTR(100500);
    $this->addToAssertionCount(1);
  }

  public function testValidateTTRException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("ttr should be >= " . Beanstalkd::TTR_MIN);
    Beanstalkd::validateTTR(Beanstalkd::TTR_MIN - 1);
  }

  public function testValidateTTRException2()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("ttr should be <= " . Beanstalkd::TTR_MAX);
    Beanstalkd::validateTTR(Beanstalkd::TTR_MAX + 1);
  }

  public function testValidatePriority()
  {
    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MIN);
    $this->addToAssertionCount(1);

    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MIN + 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MAX);
    $this->addToAssertionCount(1);

    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MAX - 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validatePriority(100500);
    $this->addToAssertionCount(1);
  }

  public function testValidatePriorityException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("priority should be >= " . Beanstalkd::PRIORITY_MIN);
    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MIN - 1);
  }

  public function testValidatePriorityException2()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("priority should be <= " . Beanstalkd::PRIORITY_MAX);
    Beanstalkd::validatePriority(Beanstalkd::PRIORITY_MAX + 1);
  }

  public function testValidateTimeout()
  {
    Beanstalkd::validateTimeout(Beanstalkd::TIMEOUT_MIN);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTimeout(Beanstalkd::TIMEOUT_MIN + 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateTimeout(100500);
    $this->addToAssertionCount(1);
  }

  public function testValidateTimeoutException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("timeout should be >= " . Beanstalkd::TIMEOUT_MIN);
    Beanstalkd::validateTimeout(Beanstalkd::TIMEOUT_MIN - 1);
  }

  public function testValidateJobID()
  {
    Beanstalkd::validateJobID(Beanstalkd::JOB_ID_MIN);
    $this->addToAssertionCount(1);

    Beanstalkd::validateJobID(Beanstalkd::JOB_ID_MIN + 1);
    $this->addToAssertionCount(1);

    Beanstalkd::validateJobID(100500);
    $this->addToAssertionCount(1);
  }

  public function testValidateJobIDException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("job id should be >= " . Beanstalkd::JOB_ID_MIN);
    Beanstalkd::validateJobID(Beanstalkd::JOB_ID_MIN - 1);
  }

  public function testValidateTubeName()
  {
    Beanstalkd::validateTubeName('some-tune-name');
    $this->addToAssertionCount(1);

    Beanstalkd::validateTubeName('A-Za-z0-9-+/;.$_()');
    $this->addToAssertionCount(1);
  }

  public function testValidateTubeNameException1()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('tube name should satisfy regexp: /^[A-Za-z0-9-+/;.$_()]{1,200}$/');
    Beanstalkd::validateTubeName('');
  }

  public function testValidateTubeNameException2()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('tube name should satisfy regexp: /^[A-Za-z0-9-+/;.$_()]{1,200}$/');
    Beanstalkd::validateTubeName(bin2hex(random_bytes(201)));
  }

  public function testSupportsCommand()
  {
    foreach (Beanstalkd::CMDS_LIST as $cmd => $_) {
      $this->assertTrue(Beanstalkd::supportsCommand($cmd));
      $this->addToAssertionCount(1);
    }

    $this->assertFalse(Beanstalkd::supportsCommand('random stuff'));
  }

  public function testSupportsResponseStatus()
  {
    foreach (Beanstalkd::STATUSES_LIST as $status => $_) {
      $this->assertTrue(Beanstalkd::supportsResponseStatus($status));
      $this->addToAssertionCount(1);
    }

    $this->assertFalse(Beanstalkd::supportsResponseStatus('random stuff'));
  }

  public function testIsErrorStatus()
  {
    foreach (Beanstalkd::ERROR_STATUSES_LIST as $status => $_) {
      $this->assertTrue(Beanstalkd::isErrorStatus($status));
      $this->addToAssertionCount(1);
    }

    $this->assertFalse(Beanstalkd::isErrorStatus(Beanstalkd::STATUS_OK));
  }

  public function testIsDataResponseStatus()
  {
    foreach (Beanstalkd::DATA_STATUSES_LIST as $status => $_) {
      $this->assertTrue(Beanstalkd::isDataResponseStatus($status));
      $this->addToAssertionCount(1);
    }

    $this->assertFalse(Beanstalkd::isDataResponseStatus(Beanstalkd::STATUS_BAD_FORMAT));
  }

  public function testParseResponseHeaders()
  {
    $this->assertEquals(
      [
        "status" => "OK",
        "headers" => ["arg1", "arg2"],
        "hasData" => true,
        "dataLength" => 123,
      ],
      Beanstalkd::parseResponseHeaders("OK arg1 arg2 123")
    );
    $this->assertEquals(
      [
        "status" => "KICKED",
        "headers" => ["arg1", "arg2", "123"],
        "hasData" => false,
        "dataLength" => 0,
      ],
      Beanstalkd::parseResponseHeaders("KICKED arg1 arg2 123")
    );
  }

  public function testParseResponseHeadersException1()
  {
    $this->expectException(ResponseException::class);
    $this->expectExceptionMessage(
      sprintf('Received invalid data length for `%s` response, expected number, got `%s`', 'OK', 'arg2')
    );
    Beanstalkd::parseResponseHeaders("OK arg1 arg2");
  }

  public function testSimpleYamlParse()
  {
    $this->assertNull(Beanstalkd::simpleYamlParse("   "));
    $this->assertEquals(
      ["default", "myAwesomeTube", "myAwesomeTube2"],
      Beanstalkd::simpleYamlParse("---\n- default\n- myAwesomeTube\n- myAwesomeTube2\n")
    );
    $this->assertEquals(
      [
        "id" => "5",
        "tube" => "myAwesomeTube",
        "state" => "delayed",
        "pri" => "1024",
        "age" => "224",
        "delay" => "5",
        "ttr" => "30",
        "time-left" => "4",
        "file" => "0",
        "reserves" => "1",
        "timeouts" => "0",
        "releases" => "1",
        "buries" => "0",
        "kicks" => "0",
      ],
      Beanstalkd::simpleYamlParse(
        "---
id: 5
tube: myAwesomeTube
state: delayed
pri: 1024
age: 224
delay: 5
ttr: 30
time-left: 4
file: 0
reserves: 1
timeouts: 0
releases: 1
buries: 0
kicks: 0
"
      )
    );
  }

  public function testSimpleYamlParseException1()
  {
    $this->expectException(ResponseException::class);
    $this->expectExceptionMessage('Failed to parse YAML string [id:]');
    Beanstalkd::simpleYamlParse("---\nid: \n");
  }

  public function testProcessStats()
  {
    $this->assertEquals(
      [
        'current-jobs-urgent' => 0,
        'current-jobs-ready' => 5,
        'current-jobs-reserved' => 0,
        'current-jobs-delayed' => 1,
        'current-jobs-buried' => 0,
        'cmd-put' => 7,
        'cmd-peek' => 0,
        'cmd-peek-ready' => 0,
        'cmd-peek-delayed' => 0,
        'cmd-peek-buried' => 0,
        'cmd-reserve' => 3,
        'cmd-reserve-with-timeout' => 1,
        'cmd-delete' => 1,
        'cmd-release' => 2,
        'cmd-use' => 7,
        'cmd-watch' => 8,
        'cmd-ignore' => 0,
        'cmd-bury' => 0,
        'cmd-kick' => 0,
        'cmd-touch' => 0,
        'cmd-stats' => 1,
        'cmd-stats-job' => 7,
        'cmd-stats-tube' => 0,
        'cmd-list-tubes' => 0,
        'cmd-list-tube-used' => 0,
        'cmd-list-tubes-watched' => 3,
        'cmd-pause-tube' => 0,
        'job-timeouts' => 0,
        'total-jobs' => 7,
        'max-job-size' => 65535,
        'current-tubes' => 4,
        'current-connections' => 1,
        'current-producers' => 1,
        'current-workers' => 0,
        'current-waiting' => 0,
        'total-connections' => 7,
        'pid' => 1,
        'version' => "1.12",
        'rusage-utime' => 0.012908,
        'rusage-stime' => 0.013901,
        'uptime' => 192868,
        'binlog-oldest-index' => 0,
        'binlog-current-index' => 0,
        'binlog-records-migrated' => 0,
        'binlog-records-written' => 0,
        'binlog-max-size' => 10485760,
        'draining' => false,
        'id' => "d29afae409ec7a2c",
        'hostname' => "24bb68330c4c",
        'os' => "#1 SMP Tue Mar 23 09:27:39 UTC 2021",
        'platform' => "x86_64",
      ],
      Beanstalkd::processStats([
        'current-jobs-urgent' => "0",
        'current-jobs-ready' => "5",
        'current-jobs-reserved' => "0",
        'current-jobs-delayed' => "1",
        'current-jobs-buried' => "0",
        'cmd-put' => "7",
        'cmd-peek' => "0",
        'cmd-peek-ready' => "0",
        'cmd-peek-delayed' => "0",
        'cmd-peek-buried' => "0",
        'cmd-reserve' => "3",
        'cmd-reserve-with-timeout' => "1",
        'cmd-delete' => "1",
        'cmd-release' => "2",
        'cmd-use' => "7",
        'cmd-watch' => "8",
        'cmd-ignore' => "0",
        'cmd-bury' => "0",
        'cmd-kick' => "0",
        'cmd-touch' => "0",
        'cmd-stats' => "1",
        'cmd-stats-job' => "7",
        'cmd-stats-tube' => "0",
        'cmd-list-tubes' => "0",
        'cmd-list-tube-used' => "0",
        'cmd-list-tubes-watched' => "3",
        'cmd-pause-tube' => "0",
        'job-timeouts' => "0",
        'total-jobs' => "7",
        'max-job-size' => "65535",
        'current-tubes' => "4",
        'current-connections' => "1",
        'current-producers' => "1",
        'current-workers' => "0",
        'current-waiting' => "0",
        'total-connections' => "7",
        'pid' => "1",
        'version' => "1.12",
        'rusage-utime' => "0.012908",
        'rusage-stime' => "0.013901",
        'uptime' => "192868",
        'binlog-oldest-index' => "0",
        'binlog-current-index' => "0",
        'binlog-records-migrated' => "0",
        'binlog-records-written' => "0",
        'binlog-max-size' => "10485760",
        'draining' => "false",
        'id' => "d29afae409ec7a2c",
        'hostname' => "24bb68330c4c",
        'os' => "#1 SMP Tue Mar 23 09:27:39 UTC 2021",
        'platform' => "x86_64",
      ])
    );
  }

  public function testProcessTubeStats()
  {
    $this->assertEquals(
      [
        'name' => "default",
        'current-jobs-urgent' => 0,
        'current-jobs-ready' => 0,
        'current-jobs-reserved' => 0,
        'current-jobs-delayed' => 0,
        'current-jobs-buried' => 0,
        'total-jobs' => 0,
        'current-using' => 0,
        'current-watching' => 1,
        'current-waiting' => 0,
        'cmd-delete' => 0,
        'cmd-pause-tube' => 0,
        'pause' => 0,
        'pause-time-left' => 0,
      ],
      Beanstalkd::processTubeStats([
        'name' => "default",
        'current-jobs-urgent' => "0",
        'current-jobs-ready' => "0",
        'current-jobs-reserved' => "0",
        'current-jobs-delayed' => "0",
        'current-jobs-buried' => "0",
        'total-jobs' => "0",
        'current-using' => "0",
        'current-watching' => "1",
        'current-waiting' => "0",
        'cmd-delete' => "0",
        'cmd-pause-tube' => "0",
        'pause' => "0",
        'pause-time-left' => "0",
      ])
    );
  }

  public function testProcessJobStats()
  {
    $this->assertEquals(
      [
        "id" => 5,
        "tube" => "myAwesomeTube",
        "state" => "delayed",
        "pri" => 1024,
        "age" => 224,
        "delay" => 5,
        "ttr" => 30,
        "time-left" => 4,
        "file" => 0,
        "reserves" => 1,
        "timeouts" => 0,
        "releases" => 1,
        "buries" => 0,
        "kicks" => 0,
      ],
      Beanstalkd::processJobStats([
        "id" => "5",
        "tube" => "myAwesomeTube",
        "state" => "delayed",
        "pri" => "1024",
        "age" => "224",
        "delay" => "5",
        "ttr" => "30",
        "time-left" => "4",
        "file" => "0",
        "reserves" => "1",
        "timeouts" => "0",
        "releases" => "1",
        "buries" => "0",
        "kicks" => "0",
      ])
    );
  }
}
