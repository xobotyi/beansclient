<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Interfaces;


    interface Response
    {
        public const OUT_OF_MEMORY   = 'OUT_OF_MEMORY';
        public const INTERNAL_ERROR  = 'INTERNAL_ERROR';
        public const BAD_FORMAT      = 'BAD_FORMAT';
        public const UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';

        //        public const ERRORS = [
        //            self::OUT_OF_MEMORY,
        //            self::INTERNAL_ERROR,
        //            self::BAD_FORMAT,
        //            self::UNKNOWN_COMMAND,
        //        ];

        public const INSERTED      = 'INSERTED';
        public const BURIED        = 'BURIED';
        public const EXPECTED_CRLF = 'EXPECTED_CRLF';
        public const JOB_TOO_BIG   = 'JOB_TOO_BIG';
        public const DRAINING      = 'DRAINING';
        public const USING         = 'USING';
        public const DEADLINE_SOON = 'DEADLINE_SOON';
        public const TIMED_OUT     = 'TIMED_OUT';
        public const RESERVED      = 'RESERVED';
        public const DELETED       = 'DELETED';
        public const NOT_FOUND     = 'NOT_FOUND';
        public const RELEASED      = 'RELEASED';
        public const WATCHING      = 'WATCHING';
        public const NOT_IGNORED   = 'NOT_IGNORED';
        public const FOUND         = 'FOUND';
        public const KICKED        = 'KICKED';
        public const OK            = 'OK';
        public const PAUSED        = 'PAUSED';
    }