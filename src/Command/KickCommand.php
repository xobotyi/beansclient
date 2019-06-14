<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class KickCommand extends Command implements CommandInterface
{
    public
    function __construct(int $count) {
        if ($count <= 0) {
            throw new CommandException("Kick count has to be a positive integer");
        }

        parent::__construct(CommandInterface::KICK, null, [$count]);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null): int {
        if ($responseHeader[0] === Response::KICKED) {
            return (int)$responseHeader[1];
        }

        throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}