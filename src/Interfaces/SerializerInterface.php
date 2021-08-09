<?php declare(strict_types=1);


namespace xobotyi\beansclient\Interfaces;


interface SerializerInterface
{
  public function serialize(mixed $data): string;

  public function deserialize(string $str): mixed;
}
