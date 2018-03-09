<?php
    /**
     * @Author : a.zinoviev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;


    class ListTubes extends CommandAbstract
    {
        public
        function getCommandStr() :string {
            return 'list-tubes';
        }

        public
        function getResponse() {

        }
    }