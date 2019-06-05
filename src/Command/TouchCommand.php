<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class TouchCommand extends Command implements CommandInterface
{

    public
    function __construct(int $jobId, ?SerializerInterface $serializer = null) {
        if ($jobId <= 0) {
            throw new CommandException('Job id must be a positive integer');
        }

        parent::__construct(CommandInterface::TOUCH, $serializer, [$jobId]);
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseBody
     *
     * @return boolean
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function processResponse(array $responseHeader, ?string $responseBody = null): bool {
        if ($responseHeader[0] === Response::TOUCHED) {
            return true;
        }
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        throw new CommandException("Got unexpected status code `${responseHeader[0]}`");
    }
}