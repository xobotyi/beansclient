<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class PutCommand extends Command implements CommandInterface
{
    public
    function __construct($payload, $priority, int $delay, int $ttr, ?SerializerInterface $serializer = null) {
        if ($delay < 0) {
            throw new CommandException('Release delay has to be >= 0');
        }

        if ($ttr < 0) {
            throw new CommandException('TTR has to be >= 0');
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

        parent::__construct(CommandInterface::PUT, $serializer, [$priority, $delay, $ttr], $payload);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null): array {
        if ($responseHeader[0] === Response::DRAINING) {
            throw new CommandException("Server is in 'drain mode', try another server or or disconnect and try later.");
        }

        if ($responseHeader[0] === Response::JOB_TOO_BIG) {
            throw new CommandException("Job's payload size exceeds max-job-size config");
        }

        if ($responseHeader[0] !== Response::INSERTED && $responseHeader[0] !== Response::BURIED) {
            throw new CommandException("Got unexpected status code `${responseHeader[0]}`");
        }

        return [
            'id'     => (int)$responseHeader[1],
            'status' => $responseHeader[0],
        ];
    }
}