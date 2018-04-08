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
     * Class StatsTube
     *
     * @package xobotyi\beansclient\Command
     */
    class StatsTube extends CommandAbstract
    {
        /**
         * @var string
         */
        private $tube;

        /**
         * StatsTube constructor.
         *
         * @param string $tube
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function __construct(string $tube) {
            if (!($tube = trim($tube))) {
                throw new Exception\Command('Tube name must be a valuable string');
            }

            $this->commandName = Interfaces\Command::STATS_TUBE;

            $this->tube = $tube;
        }

        /**
         * @return string
         */
        public function getCommandStr() :string {
            return $this->commandName . ' ' . $this->tube;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return array|null
         * @throws \Exception
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function parseResponse(array $responseHeader, ?string $responseStr) :?array {
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