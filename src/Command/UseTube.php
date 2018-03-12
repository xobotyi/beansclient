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

    class UseTube extends CommandAbstract
    {
        private $tube;

        public
        function __construct(string $tube) {
            if (!($tube = trim($tube))) {
                throw new Exception\Command('Tube name should be a valueable string');
            }

            $this->commandName = Interfaces\Command::USE;

            $this->tube        = $tube;
        }

        public
        function getCommandStr() :string {
            return $this->commandName . ' ' . $this->tube;
        }

        public
        function parseResponse(array $responseHeader, ?string $responseStr) :bool {
            if ($responseHeader[0] !== Response::USING) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }

            return $responseHeader[1] === $this->tube;
        }
    }