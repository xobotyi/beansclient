<?php


namespace xobotyi\beansclient\Command;


use xobotyi\beansclient\Exception;
use xobotyi\beansclient\Interfaces;
use xobotyi\beansclient\Response;

/**
 * Class CommandAbstract
 *
 * @package xobotyi\beansclient\Command
 */
abstract
class CommandAbstract implements Interfaces\CommandInterface
{
    /**
     * @var string
     */
    protected $commandName;
    /**
     * @var array|string|int|float
     */
    protected $payload;
    /**
     * @var \xobotyi\beansclient\Interfaces\SerializerInterface
     */
    protected $serializer;

    /**
     * @return string
     */
    public
    function __toString() :string {
        return $this->getCommandStr();
    }

    /**
     * @return mixed
     */
    public
    function getPayload() {
        return $this->payload;
    }

    /**
     * @return bool
     */
    public
    function hasPayload() :bool {
        return (bool)$this->payload;
    }

    /**
     * @param array       $responseHeader
     * @param null|string $responseStr
     *
     * @return array|mixed|null
     * @throws \Exception
     * @throws \xobotyi\beansclient\Exception\CommandException
     */
    public
    function parseResponse(array $responseHeader, ?string $responseStr) {
        if ($responseHeader[0] !== Response::OK) {
            throw new Exception\CommandException("Got unexpected status code [${responseHeader[0]}]");
        }
        else if (!$responseStr) {
            throw new Exception\CommandException('Got unexpected empty response');
        }

        return Response::YamlParse($responseStr);
    }

    /**
     * @param null|\xobotyi\beansclient\Interfaces\SerializerInterface $serialize
     *
     * @return \xobotyi\beansclient\Command\CommandAbstract
     */
    public
    function setSerializer(?Interfaces\SerializerInterface $serialize) :self {
        $this->serializer = $serialize;

        return $this;
    }
}