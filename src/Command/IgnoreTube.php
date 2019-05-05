<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class IgnoreTube
 *
 * @package xobotyi\beansclient\Command
 */
class IgnoreTube extends CommandAbstract
{
    /**
     * @var string
     */
    private $tube;

    /**
     * IgnoreTube constructor.
     *
     * @param string $tube
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public function __construct(string $tube) {
        if (!($tube = trim($tube))) {
            throw new Exception\CommandException('Tube name must be a valuable string');
        }

        $this->commandName = Interfaces\CommandInterface::IGNORE;

        $this->tube = $tube;
    }

    /**
     * @return string
     */
    public function getCommandStr() :string {
        return $this->commandName . ' ' . $this->tube;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return int
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public function parseResponse(array $responseHeader, ?string $responseStr) :int {
        if ($responseStr) {
            throw new Exception\CommandException("Unexpected response data passed");
        }
        else if ($responseHeader[0] === Response::WATCHING) {
            return (int)$responseHeader[1];
        }

        throw new Exception\CommandException("Got unexpected status code [${responseHeader[0]}]");
    }
}