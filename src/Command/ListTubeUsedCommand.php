<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Response;

class ListTubeUsedCommand extends Command implements CommandInterface
{
    public
    function __construct() {
        parent::__construct(CommandInterface::LIST_TUBE_USED);
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null) {
        if ($responseHeader[0] !== Response::OK) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        if (!$responseBody) {
            throw new CommandException(sprintf("Expected response body, got `%s`", $responseBody));
        }

        return Response::YamlParse($responseBody, false);
    }
}