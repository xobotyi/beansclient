<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class ReserveCommand extends Command implements CommandInterface
{
    public
    function __construct(int $timeout = 0, ?SerializerInterface $serializer = null) {
        if ($timeout < 0) {
            throw new CommandException("Timeout has to be >= 0");
        }

        if ($timeout) {
            parent::__construct(CommandInterface::RESERVE_WITH_TIMEOUT, $serializer, [$timeout]);
        }
        else {
            parent::__construct(CommandInterface::RESERVE, $serializer);
        }
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null) {
        if ($responseHeader[0] === Response::TIMED_OUT) {
            return null;
        }

        if ($responseHeader[0] === Response::DEADLINE_SOON) {
            return false;
        }

        if ($responseHeader[0] !== Response::RESERVED) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        return [
            'id'      => (int)$responseHeader[1],
            'payload' => ($responseBody && $this->serializer) ? $this->serializer->unserialize($responseBody) : $responseBody,
        ];
    }
}