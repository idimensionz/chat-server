<?php

namespace Tests;

use iDimensionz\ChatServer\Command\AbstractCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use Ratchet\ConnectionInterface;

class CommandTestStub extends AbstractCommand
{
    static $commandName = 'test';
    const TEST_DESCRIPTION = 'Description of command test stub';
    const TEST_HELP = 'Help message for command test stub';
    const TEST_OUTPUT = 'Congratulations!';

    /**
     * CommandTestStub constructor.
     * @param WebSocketChatServer $chatServer
     */
    public function __construct(WebSocketChatServer $chatServer)
    {
        parent::__construct($chatServer);
        $this->setDescription(self::TEST_DESCRIPTION);
        $this->setHelp(self::TEST_HELP);
    }

    /**
     * @inheritDoc
     */
    public function execute(ConnectionInterface $from, string $commandParameter)
    {
        $from->send(self::TEST_OUTPUT);
    }

    public function getChatServer(): WebSocketChatServer
    {
        return parent::getChatServer();
    }
}
