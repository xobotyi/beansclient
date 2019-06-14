<?php


namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class Peek
 *
 * @package xobotyi\beansclient\Command
 */
class Peek extends CommandAbstract
{
    public const TYPE_ID      = 'id';
    public const TYPE_READY   = 'ready';
    public const TYPE_DELAYED = 'delayed';
    public const TYPE_BURIED  = 'buried';

    private const SUBCOMMANDS = [
        self::TYPE_READY   => Interfaces\CommandInterface::PEEK_READY,
        self::TYPE_DELAYED => Interfaces\CommandInterface::PEEK_DELAYED,
        self::TYPE_BURIED  => Interfaces\CommandInterface::PEEK_BURIED,
    ];

    /**
     * @var null | int
     */
    private $jobId;

    /**
     * Peek constructor.
     *
     * @param string|number                                            $subject
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function __construct($subject, ?Interfaces\SerializerInterface $serializer = null) {
        if (is_numeric($subject)) {
            if ($subject <= 0) {
                throw new Exception\CommandException('Job id must be a positive integer');
            }

            $this->commandName = Interfaces\CommandInterface::PEEK;
            $this->jobId       = (int)$subject;
        }
        else if (is_string($subject) && isset(self::SUBCOMMANDS[$subject])) {
            $this->commandName = self::SUBCOMMANDS[$subject];
            $this->jobId       = null;
        }
        else {
            throw new Exception\CommandException("Invalid peek subject [{$subject}]");
        }

        $this->setSerializer($serializer);
    }

    /**
     * @return string
     */
    public
    function getCommandStr(): string {
        return $this->jobId
            ? $this->commandName . ' ' . $this->jobId
            : $this->commandName;
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
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return null;
        }
        else if ($responseHeader[0] !== Response::FOUND) {
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
