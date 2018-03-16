<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Interfaces;


    interface Serializer
    {
        public
        function serialize($data) :string;

        public
        function unserialize(string $str);
    }