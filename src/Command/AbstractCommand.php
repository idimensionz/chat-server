<?php

namespace iDimensionz\ChatServer\Command;

use iDimensionz\ChatServer\WebSocketChatServer;

abstract class AbstractCommand implements CommandInterface
{
    static $commandName = 'abstract';

    /**
     * @var WebSocketChatServer
     */
    private $chatServer;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $help;

    /**
     * @inheritDoc
     */
    public function __construct(WebSocketChatServer $chatServer)
    {
        $this->setChatServer($chatServer);
    }

    /**
     * @inheritDoc
     */
    static public function getCommandName(): string
    {
        return static::$commandName;
    }

    /**
     * @return WebSocketChatServer
     */
    protected function getChatServer(): WebSocketChatServer
    {
        return $this->chatServer;
    }

    /**
     * @param WebSocketChatServer $chatServer
     */
    public function setChatServer(WebSocketChatServer $chatServer): void
    {
        $this->chatServer = $chatServer;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @param string $help
     */
    public function setHelp(string $help): void
    {
        $this->help = $help;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::$commandName;
    }
}
