<?php
    /**
     * @Author : a.zinoviev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;


    use xobotyi\beansclient\Interfaces\Command;

    abstract class CommandAbstract implements Command
    {
        public
        function __toString():string {
            return $this->getCommandStr();
        }
    }