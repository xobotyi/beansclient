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
 * Class WatchTube
 *
 * @package xobotyi\beansclient\Command
 */
class WatchTube extends CommandAbstract
{
    /**
     * @var string
     */
    private $tube;

    /**
     * WatchTube constructor.
     *
     * @param string $tube
     *
     * @throws \xobotyi\beansclient\Exception\Command
     */
    public function __construct(string $tube) {
        if (!($tube = trim($tube))) {
            throw new Exception\Command('Tube name must be a valuable string');
        }

        $this->commandName = Interfaces\Command::WATCH;

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
     * @return string
     * @throws \xobotyi\beansclient\Exception\Command
     */
    public function parseResponse(array $responseHeader, ?string $responseStr) :string {
        if ($responseStr) {
            throw new Exception\Command("Unexpected response data passed");
        }
        else if ($responseHeader[0] == Response::WATCHING) {
            return (int)$responseHeader[1];
        }

        throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
    }
}