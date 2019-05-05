<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class ListTubeUsed
 *
 * @package xobotyi\beansclient\Command
 */
class ListTubeUsed extends CommandAbstract
{
    /**
     * ListTubeUsed constructor.
     */
    public function __construct() {
        $this->commandName = Interfaces\CommandInterface::LIST_TUBE_USED;
    }

    /**
     * @return string
     */
    public function getCommandStr() :string {
        return $this->commandName;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return string
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public function parseResponse(array $responseHeader, ?string $responseStr) :string {
        if ($responseStr) {
            throw new CommandException("Unexpected response data passed");
        }
        else if ($responseHeader[0] === Response::USING) {
            if (!isset($responseHeader[1])) {
                throw new CommandException("Response is missing tube name [" . implode('', $responseHeader) . "]");
            }

            return $responseHeader[1];
        }

        throw new CommandException("Got unexpected status code [${responseHeader[0]}]");
    }
}