<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class StatsCommand extends Command implements CommandInterface
{
    public
    function __construct(?SerializerInterface $serializer = null) {
        parent::__construct(CommandInterface::STATS, $serializer);
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
    function processResponse(array $responseHeader, ?string $responseBody = null): array {
        if ($responseHeader[0] !== Response::OK) {
            throw new CommandException("Got unexpected status code `${responseHeader[0]}`");
        }

        if (!$responseBody) {
            throw new CommandException(sprintf("Expected response body, got `%s`", $responseBody));
        }

        return Response::YamlParse($responseBody, true);
    }
}