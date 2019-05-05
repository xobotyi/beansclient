<?php


namespace xobotyi\beansclient\Interfaces;


/**
 * Interface Serializer
 *
 * @package xobotyi\beansclient\Interfaces
 */
interface SerializerInterface
{
    /**
     * @param $data
     *
     * @return string
     */
    public
    function serialize($data): string;

    /**
     * @param string $str
     *
     * @return mixed
     */
    public
    function unserialize(string $str);
}