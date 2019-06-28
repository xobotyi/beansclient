<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class StatsTubeCommand extends Command implements CommandInterface
{
    public
    function __construct(string $tubeName) {
        if (!($tubeName = trim($tubeName))) {
            throw new CommandException('Tube name has to be a valuable string');
        }

        parent::__construct(CommandInterface::STATS_TUBE, null, [$tubeName]);
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
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        if (!$responseBody) {
            throw new CommandException(sprintf("Expected response body, got `%s`", $responseBody));
        }

        return Response::YamlParse($responseBody, true);
    }
}