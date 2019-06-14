<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class Kick
 *
 * @package xobotyi\beansclient\Command
 */
class Kick extends CommandAbstract
{
    /**
     * @var int
     */
    private $count;

    /**
     * Kick constructor.
     *
     * @param int $count
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function __construct(int $count) {
        if ($count <= 0) {
            throw new Exception\CommandException('Kick count must be a positive integer');
        }

        $this->commandName = Interfaces\CommandInterface::KICK;

        $this->count = $count;
    }

    /**
     * @return string
     */
    public
    function getCommandStr(): string {
        return $this->commandName . ' ' . $this->count;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return int
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function parseResponse(array $responseHeader, ?string $responseStr): int {
        if ($responseStr) {
            throw new Exception\CommandException("Unexpected response data passed");
        }
        else if ($responseHeader[0] === Response::KICKED) {
            return (int)$responseHeader[1];
        }

        throw new Exception\CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
    }
}