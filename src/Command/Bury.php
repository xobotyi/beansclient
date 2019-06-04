<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class Bury
 *
 * @package xobotyi\beansclient\Command
 */
class Bury extends CommandAbstract
{
    /**
     * @var int
     */
    private $jobId;
    /**
     * @var int|float
     */
    private $priority;

    /**
     * Bury constructor.
     *
     * @param int $jobId
     * @param     $priority
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function __construct(int $jobId, $priority) {
        if ($jobId <= 0) {
            throw new Exception\CommandException('Job id must be a positive integer');
        }
        if (!is_numeric($priority)) {
            throw new Exception\CommandException('Argument 2 passed to xobotyi\beansclient\BeansClient::put() must be a number, got ' . gettype($priority));
        }
        if ($priority < 0 || $priority > Put::MAX_PRIORITY) {
            throw new Exception\CommandException('Job priority must be between 0 and ' . Put::MAX_PRIORITY);
        }

        $this->commandName = Interfaces\CommandInterface::BURY;

        $this->jobId    = $jobId;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public
    function getCommandStr(): string {
        return $this->commandName . ' ' . $this->jobId . ' ' . $this->priority;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function parseResponse(array $responseHeader, ?string $responseStr): bool {
        if ($responseStr) {
            throw new Exception\CommandException("Unexpected response data passed");
        }
        else if ($responseHeader[0] === Response::BURIED) {
            return true;
        }
        else if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        throw new Exception\CommandException("Got unexpected status code [${responseHeader[0]}]");
    }
}