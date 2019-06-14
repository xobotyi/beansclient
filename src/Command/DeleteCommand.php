<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class DeleteCommand extends Command implements CommandInterface
{
    public
    function __construct(int $jobId) {
        if ($jobId <= 0) {
            throw new CommandException('Job id must be a positive integer');
        }

        parent::__construct(CommandInterface::DELETE, null, [$jobId]);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null): bool {
        if ($responseHeader[0] === Response::DELETED) {
            return true;
        }

        if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}