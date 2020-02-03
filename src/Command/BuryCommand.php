<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class BuryCommand extends Command implements CommandInterface
{
    public
    function __construct(int $jobId, $priority)
    {
        if ($jobId <= 0) {
            throw new CommandException('Job id must be a positive integer');
        }

        if (!is_numeric($priority)) {
            throw new CommandException(sprintf('Priority has to be a number, got %s', gettype($priority)));
        }

        if ($priority < CommandInterface::PRIORITY_MINIMUM) {
            throw new CommandException(sprintf('Priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, $priority));
        }

        if ($priority > CommandInterface::PRIORITY_MAXIMUM) {
            throw new CommandException(sprintf('Priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, $priority));
        }

        parent::__construct(CommandInterface::BURY, null, [$jobId, $priority]);
    }


    public
    function processResponse(array $responseHeader, ?string $responseBody = null): bool
    {
        if ($responseHeader[0] === Response::BURIED) {
            return true;
        }

        if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}