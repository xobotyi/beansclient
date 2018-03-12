<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Interfaces;


    interface Encoder
    {
        public
        function encode($data) :string;

        public
        function decode(string $str);
    }