<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Interfaces;

/**
 * Class ListTubesWatched
 *
 * @package xobotyi\beansclient\Command
 */
class ListTubesWatched extends CommandAbstract
{
    /**
     * ListTubesWatched constructor.
     */
    public
    function __construct() {
        $this->commandName = Interfaces\CommandInterface::LIST_TUBES_WATCHED;
    }

    /**
     * @return string
     */
    public
    function getCommandStr(): string {
        return $this->commandName;
    }
}