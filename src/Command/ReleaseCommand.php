<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class ReleaseCommand extends Command implements CommandInterface
{
    public
    function __construct(int $jobId, $priority, int $delay) {
        if ($jobId <= 0) {
            throw new CommandException('Job id must be a positive integer');
        }

        if ($delay < 0) {
            throw new CommandException('Release delay has to be >= 0');
        }

        if (!is_numeric($priority)) {
            throw new CommandException(sprintf('Priority has to numeric, got %s', gettype($priority)));
        }

        if ($priority < CommandInterface::PRIORITY_MINIMUM) {
            throw new CommandException(sprintf('Priority has to be >= %d, got %d', CommandInterface::PRIORITY_MINIMUM, $priority));
        }

        if ($priority > CommandInterface::PRIORITY_MAXIMUM) {
            throw new CommandException(sprintf('Priority has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, $priority));
        }

        parent::__construct(CommandInterface::RELEASE, null, [$jobId, $priority, $delay]);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null): ?string {
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return null;
        }

        if ($responseHeader[0] === Response::RELEASED || $responseHeader[0] === Response::BURIED) {
            return $responseHeader[0];
        }

        throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}