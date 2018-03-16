<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\Interfaces;

    /**
     * Class ListTubes
     *
     * @package xobotyi\beansclient\Command
     */
    class ListTubes extends CommandAbstract
    {
        /**
         * ListTubes constructor.
         */
        public
        function __construct() {
            $this->commandName = Interfaces\Command::LIST_TUBES;
        }

        /**
         * @return string
         */
        public
        function getCommandStr() :string {
            return $this->commandName;
        }
    }