<?php


namespace xobotyi\beansclient\Command;


use PHPUnit\Framework\TestCase;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class PutCommandTest extends TestCase
{
    public
    function testCommandConstruction() {
        $command = new PutCommand('test', 2, 0, 3);

        $this->assertEquals($command->getCommandName(), CommandInterface::PUT);
        $this->assertEquals($command->getArguments(), [2, 0, 3]);
        $this->assertEquals($command->getPayload(), 'test');
    }

    public
    function testClientCommand() {
        $client = getBeansclientMock($this)
            ->setMethods(['dispatchCommand'])
            ->getMock();

        $client->expects($this->once())
               ->method('dispatchCommand')
               ->will($this->returnValue([
                                             'id'     => (int)12,
                                             'status' => Response::INSERTED,
                                         ]))
               ->with($this->isInstanceOf(PutCommand::class));

        $this->assertEquals(12, $client->put('test', 2, 0, 3)->id);
    }

    public
    function testCorrectResponse() {
        $command = new PutCommand('test', 2, 0, 3);

        $this->assertEquals($command->processResponse([Response::INSERTED, '12']), [
            'id'     => (int)12,
            'status' => Response::INSERTED,
        ]);

        $this->assertEquals($command->processResponse([Response::BURIED, '24']), [
            'id'     => (int)24,
            'status' => Response::BURIED,
        ]);
    }

    public
    function testExceptionBadDelay() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Release delay has to be >= 0');

        new PutCommand('', 0, -1, 3);
    }

    public
    function testExceptionBadTTR() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('TTR has to be >= 0');

        new PutCommand('', 0, 0, -14);
    }

    public
    function testExceptionBadPriority1() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, -1));

        new PutCommand('', -1, 0, 3);
    }

    public
    function testExceptionBadPriority2() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be a number, got %s', 'string'));

        new PutCommand('', "asd", 0, 3);
    }

    public
    function testExceptionBadPriority3() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf('Priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, CommandInterface::PRIORITY_MAXIMUM + 1));

        new PutCommand('', CommandInterface::PRIORITY_MAXIMUM + 1, 0, 3);
    }

    public
    function testExceptionDrainMode() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Server is in \'drain mode\', try another server or or disconnect and try later.');

        $command = new PutCommand('test', 2, 0, 3);
        $command->processResponse([Response::DRAINING, '24']);
    }

    public
    function testExceptionPayloadSizeExceeded() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Job\'s payload size exceeds max-job-size config');

        $command = new PutCommand('test', 2, 0, 3);
        $command->processResponse([Response::JOB_TOO_BIG, '24']);
    }

    public
    function testExceptionUndexpectedStatus() {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(sprintf("Got unexpected status code `%s`", Response::OUT_OF_MEMORY));

        $command = new PutCommand('test', 2, 0, 3);
        $command->processResponse([Response::OUT_OF_MEMORY, '24']);
    }
}
