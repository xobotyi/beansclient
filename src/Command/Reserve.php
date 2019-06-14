<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class Reserve
 *
 * @package xobotyi\beansclient\Command
 */
class Reserve extends CommandAbstract
{
    /**
     * @var int|null
     */
    private $timeout;

    /**
     * Reserve constructor.
     *
     * @param int|null                                                 $timeout
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function __construct(?int $timeout = 0, ?Interfaces\SerializerInterface $serializer = null) {
        if ($timeout < 0) {
            throw new Exception\CommandException('Timeout must be greater or equal than 0');
        }

        $this->commandName = Interfaces\CommandInterface::RESERVE;

        $this->timeout = $timeout;

        $this->setSerializer($serializer);
    }

    /**
     * @return string
     */
    public
    function getCommandStr(): string {
        return $this->timeout === null ? $this->commandName : Interfaces\CommandInterface::RESERVE_WITH_TIMEOUT . ' ' . $this->timeout;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return array|null
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function parseResponse(array $responseHeader, ?string $responseStr): ?array {
        if ($responseHeader[0] === Response::TIMED_OUT) {
            return null;
        }
        else if ($responseHeader[0] !== Response::RESERVED) {
            throw new Exception\CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }
        else if (!$responseStr) {
            throw new Exception\CommandException('Got unexpected empty response');
        }

        return [
            'id'      => (int)$responseHeader[1],
            'payload' => $this->serializer ? $this->serializer->unserialize($responseStr) : $responseStr,
        ];
    }
}