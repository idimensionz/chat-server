<?php

namespace iDimensionz\ChatServer\Command;

use iDimensionz\ChatServer\WebSocketChatServer;
use Ratchet\ConnectionInterface;

class NameCommand extends AbstractCommand
{
    static $commandName = 'name';

    /**
     * NameCommand constructor.
     * @param WebSocketChatServer $chatServer
     */
    public function __construct(WebSocketChatServer $chatServer)
    {
        parent::__construct($chatServer);
        $this->setDescription('Identifies your messages by your name. Your name will be displayed to other users in each message you send.');
        $this->setHelp('Usage: "/name <your name>"' . PHP_EOL . 'Example "/name Jim" would set your name to Jim');
    }

    /**
     * @inheritDoc
     */
    public function execute(ConnectionInterface $from, string $commandParameter)
    {
        // Change the user's username
        $this->getChatServer()->debug("Name set to '{$commandParameter}' for connection {$from->resourceId}");
        $previousName = $from->username;
        $from->username = $commandParameter;
        $encodedChatMessage = $this->getChatServer()->createEncodedSystemChatMessage("Connection {$previousName} is now known as '{$from->username}'");
        $this->getChatServer()->distributeEncodedChatMessage($from, $encodedChatMessage, false);
        $this->getChatServer()->getClients()->offsetSet($from);
        // Update all existing messages to contain new username.
        $this->getChatServer()->updateUserNameInMessages($previousName, $commandParameter);
    }
}
