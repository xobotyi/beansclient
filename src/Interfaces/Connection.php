<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient\Interfaces;


/**
 * Interface Connection
 *
 * @package xobotyi\beansclient\Interfaces
 */
interface Connection
{
    /**
     * Connection constructor.
     *
     * @param string   $host
     * @param int      $port
     * @param int|null $connectionTimeout
     * @param bool     $persistent
     */
    public
    function __construct(string $host = 'localhost', int $port = -1, int $connectionTimeout = null, bool $persistent = false);

    /**
     * @return bool
     */
    public
    function disconnect() :bool;

    /**
     * @return string
     */
    public
    function getHost() :string;

    /**
     * @return int
     */
    public
    function getPort() :int;

    /**
     * @return bool
     */
    public
    function isPersistent() :bool;

    /**
     * @return bool
     */
    public
    function isActive() :bool;

    /**
     * @param string $str
     */
    public
    function write(string $str) :void;

    /**
     * @param int $length
     *
     * @return string
     */
    public
    function read(int $length) :string;

    /**
     * @param int|null $length
     *
     * @return string
     */
    public
    function readln(int $length = null) :string;
}