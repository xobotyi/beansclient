<?php declare(strict_types=1);


namespace xobotyi\beansclient;


use JetBrains\PhpStorm\ArrayShape;
use xobotyi\beansclient\Exceptions\CommandException;
use xobotyi\beansclient\Interfaces\SerializerInterface;

class Command
{
  /**
   * @throws CommandException
   */
  public function __construct(
    private string $command,
    private array  $expectedStatuses = [],
    private bool   $payloadBody = false,
    private bool   $yamlBody = false,
  )
  {
    if (!Beanstalkd::supportsCommand($this->command)) {
      throw new CommandException(sprintf('Unknown beanstalkd command `%s`', $this->command));
    }

    $expected = [];

    foreach ($this->expectedStatuses as $status) {
      if (!Beanstalkd::supportsResponseStatus($status)) {
        throw new CommandException(sprintf('Unknown beanstalkd response status `%s`', $status));
      }

      $expected[$status] = true;
    }

    $this->expectedStatuses = $expected;
  }

  /**
   * Build command string
   *
   * @param array $args
   * @param mixed|null $payload
   * @return string
   */
  public function buildCommand(array $args = [], mixed $payload = null): string
  {
    $chunks = [$this->command, ...$args];

    if ($payload === null) {
      return join(" ", $chunks) . Beanstalkd::CRLF;
    }

    $chunks[] = mb_strlen($payload, "8BIT");

    return join(" ", $chunks) . Beanstalkd::CRLF . $payload . Beanstalkd::CRLF;
  }

  /**
   * @throws CommandException
   */
  #[ArrayShape(["status" => "string", "headers" => "string[]", 'data' => "null|string",])]
  public function handleResponse(array $response, ?SerializerInterface $serializer): array
  {
    if (Beanstalkd::isErrorStatus($response['status'])) {
      throw new CommandException(
        sprintf('Error status `%s` received in response to `%s` command', $response['status'], $this->command)
      );
    }

    if (empty($this->expectedStatuses[$response['status']])) {
      throw new CommandException(
        sprintf('Unexpected status `%s` received in response to `%s` command', $response['status'], $this->command)
      );
    }

    $result = [
      "status" => $response['status'],
      "headers" => $response['headers'],
      "data" => null,
    ];

    if (!empty($response['data'])) {
      $result['data'] = mb_substr($response['data'], 0, -Beanstalkd::CRLF_LEN, '8BIT');

      if ($this->payloadBody) {
        if ($serializer) {
          $result['data'] = $serializer->deserialize($result['data']);
        }
      } else if ($this->yamlBody) {
        $result['data'] = Beanstalkd::simpleYamlParse($result['data']);
      }
    }

    return $result;
  }
}
