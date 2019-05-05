<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class Delete
 *
 * @package xobotyi\beansclient\Command
 */
class Delete extends CommandAbstract
{
    /**
     * @var int
     */
    private $jobId;

    /**
     * Delete constructor.
     *
     * @param int $jobId
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public function __construct(int $jobId) {
        if ($jobId <= 0) {
            throw new Exception\CommandException('Job id must be a positive integer');
        }

        $this->commandName = Interfaces\CommandInterface::DELETE;

        $this->jobId = $jobId;
    }

    /**
     * @return string
     */
    public function getCommandStr() :string {
        return $this->commandName . ' ' . $this->jobId;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return bool
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public function parseResponse(array $responseHeader, ?string $responseStr) :bool {
        if ($responseStr) {
            throw new Exception\CommandException("Unexpected response data passed");
        }
        else if ($responseHeader[0] === Response::DELETED) {
            return true;
        }
        else if ($responseHeader[0] === Response::NOT_FOUND) {
            return false;
        }

        throw new Exception\CommandException("Got unexpected status code [${responseHeader[0]}]");
    }
}