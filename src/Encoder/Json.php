<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Encoder;


    use xobotyi\beansclient\Interfaces\Encoder;

    class Json implements Encoder
    {
        public
        function encode($data, ?int $options = null, int $depth = 512) :string {
            return json_encode($data, $options, $depth);
        }

        public
        function decode(string $str, bool $assoc = true, ?int $options = null, int $depth = 512) {
            return json_decode($str, $assoc, $depth, $options);
        }
    }