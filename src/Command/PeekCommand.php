<?php
declare(strict_types=1);

namespace xobotyi\beansclient\Command;

use xobotyi\beansclient\Command;
use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\CommandInterface;
use xobotyi\beansclient\Interfaces\SerializerInterface;
use xobotyi\beansclient\Response;

class PeekCommand extends Command implements CommandInterface
{
    public const TYPE_READY   = 'ready';
    public const TYPE_DELAYED = 'delayed';
    public const TYPE_BURIED  = 'buried';

    private const SUBCOMMANDS = [
        self::TYPE_READY   => CommandInterface::PEEK_READY,
        self::TYPE_DELAYED => CommandInterface::PEEK_DELAYED,
        self::TYPE_BURIED  => CommandInterface::PEEK_BURIED,
    ];

    public
    function __construct($subject, ?SerializerInterface $serializer = null) {
        if (is_numeric($subject)) {
            if ($subject <= 0) {
                throw new CommandException('Job id must be a positive integer');
            }

            parent::__construct(CommandInterface::PEEK, $serializer, [$subject]);
        }
        else if (self::SUBCOMMANDS[$subject] ?? false) {
            parent::__construct(self::SUBCOMMANDS[$subject], $serializer);
        }
        else {
            throw new CommandException(sprintf('Invalid peek subject `%s`', $subject));
        }
    }

    public
    function processResponse(array $responseHeader, ?string $responseBody = null) {
        if ($responseHeader[0] === Response::NOT_FOUND) {
            return null;
        }

        if ($responseHeader[0] !== Response::FOUND) {
            throw new CommandException(sprintf('Got unexpected status code `%s`', $responseHeader[0]));
        }

        if (!$responseBody) {
            throw new CommandException(sprintf('Expected response body, got `%s`', $responseBody));
        }

        return ($responseBody && $this->serializer) ? $this->serializer->unserialize($responseBody) : $responseBody;
    }
}
