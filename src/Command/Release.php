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

    class Release extends CommandAbstract
    {
        private $jobId;
        private $priority;
        private $delay;

        public
        function __construct(int $jobId, int $priority, int $delay) {
            if ($jobId <= 0) {
                throw new Exception\Command('Job id must be a positive integer');
            }
            if ($priority < 0 || $priority > Put::MAX_PRIORITY) {
                throw new Exception\Command('Job priority must be between 0 and ' . Put::MAX_PRIORITY);
            }
            if ($delay < 0) {
                throw new Exception\Command('Job delay must be a positive integer');
            }

            $this->commandName = Interfaces\Command::RELEASE;

            $this->jobId    = $jobId;
            $this->priority = $priority;
            $this->delay    = $delay;
        }

        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->jobId . ' ' . $this->priority . ' ' . $this->delay;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :?string {
            if ($responseStr) {
                throw new Exception\Command("Unexpected response data passed");
            }
            else if ($responseHeader[0] === Response::RELEASED || $responseHeader[0] === Response::BURIED) {
                return $responseHeader[0];
            }
            else if ($responseHeader[0] === Response::NOT_FOUND) {
                return null;
            }

            throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
        }
    }