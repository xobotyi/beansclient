<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class StatsJobCommand extends Command implements CommandInterface
{
    public
    function __construct(int $jobId, ?SerializerInterface $serializer = null) {
        if ($jobId <= 0) {
            throw new CommandException('Job id must be a positive integer');
        }

        parent::__construct(CommandInterface::STATS_JOB, $serializer, [$jobId]);
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseBody
     *
     * @return null | array
     * @throws \xobotyi\beansclient\Exception\CommandException
     * @throws \Exception
     */
    public
    function processResponse(array $responseHeader, ?string $responseBody = null): ?array {
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return null;
        }

        if ($responseHeader[0] !== Response::OK) {
            throw new CommandException("Got unexpected status code `${responseHeader[0]}`");
        }

        if (!$responseBody) {
            throw new CommandException(sprintf("Expected response body, got `%s`", $responseBody));
        }

        return Response::YamlParse($responseBody, true);
    }
}