<?php

namespace iDimensionz\ChatServer\Command;

use iDimensionz\ChatServer\WebSocketChatServer;
use Ratchet\ConnectionInterface;

interface CommandInterface
{
    /**
     * CommandInterface constructor.
     * @param WebSocketChatServer $chatServer
     */
    public function __construct(WebSocketChatServer $chatServer);

    /**
     * Returns the command string that this class responds to.
     * @return string
     */
    static public function getCommandName(): string;

    /**
     * Returns a brief description of what this command does.
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns help on how to use the command including an example.
     * @return string
     */
    public function getHelp(): string;

    /**
     * Executes the command.
     * @param ConnectionInterface $from
     * @param string $commandParameter
     * @return mixed
     */
    public function execute(ConnectionInterface $from, string $commandParameter);
}