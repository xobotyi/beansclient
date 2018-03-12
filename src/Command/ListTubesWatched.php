<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\Interfaces;

    class ListTubesWatched extends CommandAbstract
    {
        public
        function __construct() {
            $this->commandName = Interfaces\Command::LIST_TUBES_WATCHED;
        }

        public
        function getCommandStr() :string {
            return $this->commandName;
        }
    }