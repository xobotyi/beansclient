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

    class Delete extends CommandAbstract
    {
        private $jobId;

        public
        function __construct(int $jobId) {
            if ($jobId <= 0) {
                throw new Exception\Command('Job id should be a positive integer');
            }

            $this->commandName = Interfaces\Command::DELETE;

            $this->jobId = $jobId;
        }

        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->jobId;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :bool {
            if ($responseHeader[0] === Response::DELETED) {
                return true;
            }
            else {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            // ToDo: make handle of NOT_FOUND status
        }
    }