<?php declare(strict_types=1);

namespace xobotyi\beansclient\Serializer;

use xobotyi\beansclient\Interfaces\SerializerInterface;

class JsonSerializer implements SerializerInterface
{
  public function serialize(mixed $data): string
  {
    return json_encode($data);
  }

  public function deserialize(string $str): mixed
  {
    return json_decode($str);
  }
}
