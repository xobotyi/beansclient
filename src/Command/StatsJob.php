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

    class StatsJob extends CommandAbstract
    {
        private $jobId;

        public
        function __construct(int $jobId) {
            if ($jobId <= 0) {
                throw new Exception\Command('Job id should be a positive integer');
            }

            $this->commandName = Interfaces\Command::STATS_JOB;

            $this->jobId = $jobId;
        }

        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->jobId;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :?array {
            if ($responseHeader[0] === Response::NOT_FOUND) {
                return null;
            }
            else if ($responseHeader[0] !== Response::OK) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            return Response::YamlParse($responseStr, true);
        }
    }