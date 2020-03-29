<?php

namespace iDimensionz\ChatServer\Command;

use iDimensionz\ChatServer\WebSocketChatServer;
use Ratchet\ConnectionInterface;

class DebugCommand extends AbstractCommand
{
    static $commandName = 'debug';

    const DEBUG_MESSAGES = 'messages';

    /**
     * NameCommand constructor.
     * @param WebSocketChatServer $chatServer
     */
    public function __construct(WebSocketChatServer $chatServer)
    {
        parent::__construct($chatServer);
        $this->setDescription('Dumps messages to the chat server console. Only useful for developers.');
        $this->setHelp('Usage: "/debug <debug type>"' . PHP_EOL . 'Example "/debug ' . self::DEBUG_MESSAGES . '" would dump the current list of JSON encoded messages to the chat server console.');
    }

    /**
     * @inheritDoc
     */
    public function execute(ConnectionInterface $from, string $commandParameter)
    {
        switch ($commandParameter) {
            case self::DEBUG_MESSAGES:
                // Output all of the messages to the chat server's console.
                echo implode(PHP_EOL, $this->getChatServer()->getMessages());
                echo PHP_EOL;
                $from->send('Debug-' . self::DEBUG_MESSAGES . ': completed successfully.');
                break;
        }
    }
}
