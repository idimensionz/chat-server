<?php

namespace Tests;

use iDimensionz\ChatServer\Command\CommandInterface;
use iDimensionz\ChatServer\WebSocketChatServer;

class WebSocketChatServerTestStub extends WebSocketChatServer
{
    public function addAvailableCommand(CommandInterface $availableCommand)
    {
        parent::addAvailableCommand($availableCommand);
    }

    public function getAvailableCommands(): array
    {
        return parent::getAvailableCommands();
    }

    public function registerCommands()
    {
        parent::registerCommands();
    }
}
