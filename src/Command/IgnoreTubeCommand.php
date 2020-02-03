<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class IgnoreTubeCommand extends Command implements CommandInterface
{
    public
    function __construct(string $tubeName)
    {
        if (!($tubeName = trim($tubeName))) {
            throw new CommandException('Tube name has to be a valuable string');
        }

        parent::__construct(CommandInterface::IGNORE, null, [$tubeName]);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null): ?int
    {
        if ($responseHeader[0] === Response::NOT_IGNORED) {
            return null;
        }

        if ($responseHeader[0] !== Response::WATCHING) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        return (int)$responseHeader[1];
    }
}