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
     * Class Delete
     *
     * @package xobotyi\beansclient\Command
     */
    class Delete extends CommandAbstract
    {
        /**
         * @var int
         */
        private $jobId;

        /**
         * Delete constructor.
         *
         * @param int $jobId
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public
        function __construct(int $jobId) {
            if ($jobId <= 0) {
                throw new Exception\Command('Job id must be a positive integer');
            }

            $this->commandName = Interfaces\Command::DELETE;

            $this->jobId = $jobId;
        }

        /**
         * @return string
         */
        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->jobId;
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
            else if ($responseHeader[0] === Response::DELETED) {
                return true;
            }
            else if ($responseHeader[0] === Response::NOT_FOUND) {
                return false;
            }

            throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
        }
    }