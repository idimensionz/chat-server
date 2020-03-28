<?php

namespace MyApp\Command;

use MyApp\Chat;

abstract class AbstractCommand implements CommandInterface
{
    static $commandName = 'abstract';

    /**
     * @var Chat
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
    public function __construct(Chat $chatServer)
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
     * @return Chat
     */
    protected function getChatServer(): Chat
    {
        return $this->chatServer;
    }

    /**
     * @param Chat $chatServer
     */
    public function setChatServer(Chat $chatServer): void
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
}
