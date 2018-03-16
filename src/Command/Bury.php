<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    namespace xobotyi\beansclient\Command;

    use xobotyi\beansclient\Exception;
    use xobotyi\beansclient\Interfaces;
    use xobotyi\beansclient\Response;

    /**
     * Class Bury
     *
     * @package xobotyi\beansclient\Command
     */
    class Bury extends CommandAbstract
    {
        /**
         * @var int
         */
        private $jobId;
        /**
         * @var int|float
         */
        private $priority;

        /**
         * Bury constructor.
         *
         * @param int $jobId
         * @param     $priority
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function __construct(int $jobId, $priority) {
            if ($jobId <= 0) {
                throw new Exception\Command('Job id must be a positive integer');
            }
            if (!is_numeric($priority)) {
                throw new Exception\Command('Argument 2 passed to xobotyi\beansclient\BeansClient::put() must be a number, got ' . gettype($priority));
            }
            if ($priority < 0 || $priority > Put::MAX_PRIORITY) {
                throw new Exception\Command('Job priority must be between 0 and ' . Put::MAX_PRIORITY);
            }

            $this->commandName = Interfaces\Command::BURY;

            $this->jobId    = $jobId;
            $this->priority = $priority;
        }

        /**
         * @return string
         */
        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->jobId . ' ' . $this->priority;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return bool
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function parseResponse(array $responseHeader, ?string $responseStr) :bool {
            if ($responseStr) {
                throw new Exception\Command("Unexpected response data passed");
            }
            else if ($responseHeader[0] === Response::BURIED) {
                return true;
            }
            else if ($responseHeader[0] === Response::NOT_FOUND) {
                return false;
            }

            throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
        }
    }