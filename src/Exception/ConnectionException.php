<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

namespace xobotyi\beansclient\Exception;


/**
 * Class Connection
 *
 * @package xobotyi\beansclient\Exception
 */
class ConnectionException extends \Exception
{
    /**
     * Connection constructor.
     *
     * @param int    $errNo
     * @param string $errStr
     */
    public function __construct(int $errNo, string $errStr) {
        parent::__construct("Connection error {$errNo}: {$errStr}");
    }
}