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
     * Class Peek
     *
     * @package xobotyi\beansclient\Command
     */
    class Peek extends CommandAbstract
    {
        public const TYPE_ID      = 'id';
        public const TYPE_READY   = 'ready';
        public const TYPE_DELAYED = 'delayed';
        public const TYPE_BURIED  = 'buried';

        private const SUBCOMMANDS = [
            self::TYPE_READY   => self::PEEK_READY,
            self::TYPE_DELAYED => self::PEEK_DELAYED,
            self::TYPE_BURIED  => self::PEEK_BURIED,
        ];

        /**
         * @var null
         */
        private $jobId;

        /**
         * Peek constructor.
         *
         * @param                                                 $subject
         * @param null|\xobotyi\beansclient\Interfaces\Serializer $serializer
         *
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function __construct($subject, ?Interfaces\Serializer $serializer = null) {
            if (is_numeric($subject)) {
                if ($subject <= 0) {
                    throw new Exception\Command('Job id must be a positive integer');
                }

                $this->commandName = Interfaces\Command::PEEK;
                $this->jobId       = (int)$subject;
            }
            else if (is_string($subject) && isset(self::SUBCOMMANDS[$subject])) {
                $this->commandName = self::SUBCOMMANDS[$subject];
                $this->jobId       = null;
            }
            else {
                throw new Exception\Command("Invalid peek subject [{$subject}]");
            }

            $this->setSerializer($serializer);
        }

        /**
         * @return string
         */
        public function getCommandStr() :string {
            return $this->jobId
                ? $this->commandName . ' ' . $this->jobId
                : $this->commandName;
        }

        /**
         * @param array       $responseHeader
         * @param null|string $responseStr
         *
         * @return array|null
         * @throws \xobotyi\beansclient\Exception\Command
         */
        public function parseResponse(array $responseHeader, ?string $responseStr) :?array {
            if ($responseHeader[0] === Response::NOT_FOUND) {
                return null;
            }
            else if ($responseHeader[0] !== Response::FOUND) {
                throw new Exception\Command("Got unexpected status code [${responseHeader[0]}]");
            }
            else if (!$responseStr) {
                throw new Exception\Command('Got unexpected empty response');
            }

            return [
                'id'      => (int)$responseHeader[1],
                'payload' => $this->serializer ? $this->serializer->unserialize($responseStr) : $responseStr,
            ];
        }
    }