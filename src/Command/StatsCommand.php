<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use Exception;
use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class StatsCommand extends Command implements CommandInterface
{
    public
    function __construct()
    {
        parent::__construct(CommandInterface::STATS);
    }

    /**
     * @param array $responseHeader
     * @param null|string $responseBody
     *
     * @return null | array
     * @throws CommandException
     * @throws Exception
     */
    public
    function processResponse(array $responseHeader, ?string $responseBody = null): array
    {
        if ($responseHeader[0] !== Response::OK) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        if (!$responseBody) {
            throw new CommandException(sprintf('Expected response body, got `%s`', $responseBody));
        }

        return Response::YamlParse($responseBody, true);
    }
}
