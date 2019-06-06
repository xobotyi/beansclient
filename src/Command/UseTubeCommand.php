<?php
declare(strict_types=1);


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class UseTubeCommand extends Command implements CommandInterface
{
    public
    function __construct(string $tubeName, ?SerializerInterface $serializer = null) {
        if (!($tubeName = trim($tubeName))) {
            throw new CommandException('Tube name has to be a valuable string');
        }

        parent::__construct(CommandInterface::USE, $serializer, [$tubeName]);
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseBody
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function processResponse(array $responseHeader, ?string $responseBody = null): string {
        if ($responseHeader[0] !== Response::USING) {
            throw new CommandException("Got unexpected status code `${responseHeader[0]}`");
        }

        return $responseHeader[1];
    }
}