<?php

namespace MyApp\Command;

use MyApp\Chat;
use Ratchet\ConnectionInterface;

class NameCommand extends AbstractCommand
{
    static $commandName = 'name';

    /**
     * NameCommand constructor.
     * @param Chat $chatServer
     */
    public function __construct(Chat $chatServer)
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
        echo "Name set to '{$commandParameter}' for connection {$from->resourceId}" . PHP_EOL;
        $previousName = $from->username;
        $from->username = $commandParameter;
        $encodedChatMessage = $this->getChatServer()->createEncodedSystemChatMessage("Connection {$previousName} is now known as '{$from->username}'");
        $this->getChatServer()->distributeEncodedChatMessage($from, $encodedChatMessage, false);
        $this->getChatServer()->getClients()->offsetSet($from);
        // Update all existing messages to contain new username.
        $this->getChatServer()->updateUserNameInMessages($previousName, $commandParameter);
    }
}
