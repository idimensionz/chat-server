<?php

namespace Tests;

use iDimensionz\ChatServer\Command\CommandInterface;
use iDimensionz\ChatServer\WebSocketChatServer;
use Ratchet\ConnectionInterface;

class WebSocketChatServerTestStub extends WebSocketChatServer
{
    /**
     * @param CommandInterface $availableCommand
     */
    public function addAvailableCommand(CommandInterface $availableCommand)
    {
        parent::addAvailableCommand($availableCommand);
    }

    /**
     * @return array
     */
    public function getAvailableCommands(): array
    {
        return parent::getAvailableCommands();
    }

    public function registerCommands()
    {
        parent::registerCommands();
    }

    /**
     * @param ConnectionInterface $from
     * @param $message
     * @throws \Exception
     */
    public function processCommand(ConnectionInterface $from, $message)
    {
        parent::processCommand($from, $message);
    }

    /**
     * @param ConnectionInterface $from
     * @return string
     */
    public function getClientUserName(ConnectionInterface $from)
    {
        return parent::getClientUserName($from);
    }

    /**
     * @param mixed $message
     */
    public function debug($message)
    {
        parent::debug($message);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return parent::getMessages();
    }

    public function setMessages(array $messages): void
    {
        parent::setMessages($messages);
    }
}
