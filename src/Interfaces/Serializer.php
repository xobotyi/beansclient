<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Interfaces;


    /**
     * Interface Serializer
     *
     * @package xobotyi\beansclient\Interfaces
     */
    interface Serializer
    {
        /**
         * @param $data
         *
         * @return string
         */
        public
        function serialize($data) :string;

        /**
         * @param string $str
         *
         * @return mixed
         */
        public
        function unserialize(string $str);
    }