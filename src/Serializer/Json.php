<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Serializer;


    use xobotyi\beansclient\Interfaces\Serializer;

    class Json implements Serializer
    {
        public
        function serialize($data) :string {
            return json_encode($data);
        }

        public
        function unserialize(string $str) {
            return json_decode($str, true);
        }
    }