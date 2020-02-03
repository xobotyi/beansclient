<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class WatchTubeCommand extends Command implements CommandInterface
{
    public
    function __construct(string $tubeName)
    {
        if (!($tubeName = trim($tubeName))) {
            throw new CommandException('Tube name has to be a valuable string');
        }

        parent::__construct(CommandInterface::WATCH, null, [$tubeName]);
    }

    /**
     * @param array $responseHeader
     * @param null|string $responseBody
     *
     * @return string
     * @throws CommandException
     */
    public
    function processResponse(array $responseHeader, ?string $responseBody = null): string
    {
        if ($responseHeader[0] !== Response::WATCHING) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        return $responseHeader[1];
    }
}