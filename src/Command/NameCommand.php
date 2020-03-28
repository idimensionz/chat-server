<?php

namespace MyApp\Command;

use MyApp\Chat;
use Ratchet\ConnectionInterface;

class NameCommand extends AbstractCommand
{
    static $commandName = 'name';

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
        echo "Name set to '{$commandParameter}' for connection {$from->resourceId}" . PHP_EOL;
        $from->username = $commandParameter;
        $encodedChatMessage = $this->getChatServer()->createEncodedSystemChatMessage("Connection {$from->resourceId} is now known as '{$from->username}'");
        $this->getChatServer()->distributeEncodedChatMessage($from, $encodedChatMessage, false);
        $this->getChatServer()->getClients()->offsetSet($from);
    }
}
