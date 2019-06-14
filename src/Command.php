<?php
declare(strict_types=1);

namespace xobotyi\beansclient;

use xobotyi\beansclient\Exception\CommandException;
use xobotyi\beansclient\Interfaces\SerializerInterface;

class Command
{
    public const MAX_PAYLOAD_SIZE = 65536;

    protected $crlf = BeansClientOld::CRLF;

    /**
     * @var string
     */
    protected $commandName;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array|string|int|float
     */
    protected $payload = null;

    /**
     * @var int
     */
    protected $payloadSize = 0;

    protected $rawPayload = null;

    /**
     * @var \xobotyi\beansclient\Interfaces\SerializerInterface
     */
    protected $serializer;

    public
    function __construct(string $commandName, ?SerializerInterface $serializer = null, ?array $arguments = [], $payload = null) {
        $this->commandName = $commandName;
        $this->arguments   = $arguments ?: [];

        $this->setSerializer($serializer)
             ->setPayload($payload);
    }

    public
    function getCommandName(): string {
        return $this->commandName;
    }

    public
    function getArguments(): array {
        return $this->arguments;
    }

    /**
     * Sets the payload's serializer
     *
     * @param \xobotyi\beansclient\Interfaces\SerializerInterface $serializer
     *
     * @return $this
     */
    public
    function setSerializer(?SerializerInterface $serializer) {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Returns the payload's serializer
     *
     * @return null|\xobotyi\beansclient\Interfaces\SerializerInterface
     */
    public
    function getSerializer(): ?SerializerInterface {
        return $this->serializer;
    }

    /**
     * Returns current serialized payload
     *
     * @return null|string
     */
    public
    function getPayload(): ?string {
        return $this->payload;
    }

    /**
     * Returns unserialized payload
     *
     * @return mixed
     */
    public
    function getRawPayload() {
        return $this->rawPayload;
    }

    /**
     * Returns true if command has non-null payload
     *
     * @return bool
     */
    public
    function hasPayload(): bool {
        return $this->payload !== null;
    }

    /**
     * @param $payload
     *
     * @return $this
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function setPayload($payload) {
        $this->rawPayload = $payload;

        if (!$payload) {
            $this->payload     = null;
            $this->payloadSize = 0;

            return $this;
        }

        if ($this->serializer) {
            $payload = $this->serializer->serialize($payload);
        }
        else if (!is_numeric($payload) && !is_string($payload)) {
            throw new CommandException("No serializer provided, payload has to be a string or a number. Configure serializer or cast payload to the string manually.");
        }
        else {
            $payload = (string)$payload;
        }

        $this->payloadSize = mb_strlen($payload, "8bit");
        if ($this->payloadSize > self::MAX_PAYLOAD_SIZE) {
            throw new CommandException(sprintf("Maximum payload size is %s bytes, got %s.", self::MAX_PAYLOAD_SIZE, $this->payloadSize));
        }

        $this->payload = $payload;

        return $this;
    }

    /**
     * Returns built command string
     *
     * @return string
     */
    public
    function buildCommand(): string {
        $parts = [$this->commandName];

        if ($this->arguments) {
            $parts[] = implode(" ", $this->arguments);
        }

        if ($this->payloadSize) {
            $parts[] = $this->payloadSize . $this->crlf . $this->payload;
        }

        return implode(" ", $parts);
    }

    public
    function __toString(): string {
        return $this->buildCommand();
    }
}