<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class PauseCommand extends Command implements CommandInterface
{
    public
    function __construct(string $tubeName, $delay) {
        if (!($tubeName = trim($tubeName))) {
            throw new CommandException('Tube name has to be a valuable string');
        }

        if (!is_numeric($delay)) {
            throw new CommandException(sprintf('Delay has to be a number, got %s', gettype($delay)));
        }

        if ($delay < 0) {
            throw new CommandException(sprintf('Delay has to be >= %d, got %d', 0, $delay));
        }

        if ($delay > CommandInterface::PRIORITY_MAXIMUM) {
            throw new CommandException(sprintf('Delay has to be <= %d, got %d', CommandInterface::PRIORITY_MAXIMUM, $delay));
        }

        parent::__construct(CommandInterface::PAUSE_TUBE, null, [$tubeName, $delay]);
    }


    public
    function processResponse(array $responseHeader, ?string $responseBody = null): bool {
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        if ($responseHeader[0] === Response::PAUSED) {
            return true;
        }

        throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}