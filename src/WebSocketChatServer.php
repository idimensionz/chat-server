<?php
namespace iDimensionz\ChatServer;

use iDimensionz\ChatServer\Command\CommandInterface;
use iDimensionz\ChatServer\Command\DebugCommand;
use iDimensionz\ChatServer\Command\NameCommand;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketChatServer implements MessageComponentInterface
{
    const COMMAND_PREFIX = '/';

    const USER_NAME_SYSTEM = 'Chat Server';

    const DEBUG_MODE = 'CHAT_SERVER_DEBUG';

    /**
     * @var \SplObjectStorage
     */
    private $clients;
    /**
     * @var array
     */
    private $messages;
    /**
     * @var array
     */
    private $availableCommands;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->messages = [];
        $this->registerCommands();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $conn->username = "id {$conn->resourceId}";
        $this->clients->attach($conn);

        $message = "New connection! ({$conn->username})";
        $this->debug($message);
        $encodedChatMessage = $this->createEncodedSystemChatMessage($message);
        $this->distributeEncodedChatMessage($conn, $encodedChatMessage);
        foreach ($this->messages as $message) {
            $conn->send($message);
        }
    }

    /**
     * @param ConnectionInterface $from
     * @param string $message
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $recipientCount = count($this->clients) - 1;
        $this->debug(sprintf(
            'Connection %d sending message "%s" to %d other connection%s',
            $from->resourceId,
            $message,
            $recipientCount,
            $recipientCount == 1 ? '' : 's'
        ));
        if (self::COMMAND_PREFIX == substr($message, 0, 1)) {
            $this->processCommand($from, $message);
        } else {
            $encodedChatMessage = $this->createEncodedChatMessage($from, $message);
            $this->messages[] = $encodedChatMessage;
            $this->distributeEncodedChatMessage($from, $encodedChatMessage);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $message = "Connection {$conn->username} has disconnected";
        $this->debug($message);
        $encodedChatMessage = $this->createEncodedSystemChatMessage($message);
        $this->distributeEncodedChatMessage($conn, $encodedChatMessage);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->debug("An error has occurred: {$e->getMessage()}");

        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param $message
     * @throws \Exception
     */
    protected function processCommand(ConnectionInterface $from, $message)
    {
        $this->debug("Processing command message: '{$message}'");
        $pieces = explode(' ', $message);
        $command = explode('/', $pieces[0])[1];
        unset($pieces[0]);
        $commandParameter = implode(' ', $pieces);
        $availableCommands = $this->getAvailableCommands();
        if (isset($availableCommands[$command])) {
            $availableCommands[$command]->execute($from, $commandParameter);
        } else {
            $encodedChatMessage = $this->createEncodedChatMessage($from, "'{$message}' is not a valid command");
            $from->send($encodedChatMessage);
        }
    }

    /**
     * @param ConnectionInterface $from
     * @param $message
     * @return false|string
     */
    public function createEncodedChatMessage(ConnectionInterface $from, $message)
    {
        $clientUserName = $this->getClientUserName($from);
        $chatMessage = new ChatMessage();
        $chatMessage->setMessage($message);
        $chatMessage->setUserName($clientUserName);

        return json_encode($chatMessage);
    }

    /**
     * @param $message
     * @return false|string
     */
    public function createEncodedSystemChatMessage($message)
    {
        $chatMessage = new ChatMessage();
        $chatMessage->setIsSystemMessage(true);
        $chatMessage->setMessage($message);
        $chatMessage->setUserName(self::USER_NAME_SYSTEM);

        return json_encode($chatMessage);
    }

    /**
     * Sends a message to all connections.
     * @param ConnectionInterface $from
     * @param string $encodedChatMessage
     * @param bool $skipSender  Skips sending the message back to the sender when true.
     */
    public function distributeEncodedChatMessage(
        ConnectionInterface $from,
        string $encodedChatMessage,
        bool $skipSender = true
    ): void {
        /**
         * @var ConnectionInterface $client
         */
        foreach ($this->getClients() as $client) {
            if (!$skipSender || $from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($encodedChatMessage);
            }
        }
    }

    /**
     * Get the client's user name from the meta-data added to the matching connection.
     * @param ConnectionInterface $from
     * @return string
     */
    protected function getClientUserName(ConnectionInterface $from)
    {
        $clientUserName = '';
        // Find the matching connection.
        foreach ($this->getClients() as $client) {
            $this->debug($client->resourceId);
            if ($from == $client) {
                $this->debug("Found match!");
                $clientUserName = $client->username;
                $this->debug("Match's username: {$clientUserName}");
            }
        }
        //        $client = $this->clients->offsetGet($from);
        return $clientUserName;
    }

    /**
     * Update username in messages for a particular user.
     * @param string $previousUserName
     * @param string $newUserName
     */
    public function updateUserNameInMessages(string $previousUserName, string $newUserName)
    {
        foreach ($this->getMessages() as $key => $message) {
            $chatMessage = json_decode($message);
            if ($previousUserName == $chatMessage->userName) {
                $chatMessage->userName = $newUserName;
                $message = json_encode($chatMessage);
                $this->messages[$key] = $message;
            }
        }
    }

    protected function registerCommands()
    {
        // @todo Iterate through the classes in Command dir and register each class that implements CommandInterface.
        $this->addAvailableCommand(new NameCommand($this));
        $this->addAvailableCommand(new DebugCommand($this));
    }

    /**
     * @return \SplObjectStorage
     */
    public function getClients(): \SplObjectStorage
    {
        return $this->clients;
    }

    /**
     * @param \SplObjectStorage $clients
     */
    public function setClients(\SplObjectStorage $clients): void
    {
        $this->clients = $clients;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    protected function getAvailableCommands(): array
    {
        return $this->availableCommands;
    }

    /**
     * @param array $availableCommands
     */
    public function setAvailableCommands(array $availableCommands): void
    {
        $this->availableCommands = $availableCommands;
    }

    /**
     * @param CommandInterface $availableCommand
     */
    protected function addAvailableCommand(CommandInterface $availableCommand)
    {
        $this->availableCommands[$availableCommand::getCommandName()] = $availableCommand;
    }

    /**
     * When debug mode is enabled (i.e. environment variable is set to 1), then echo out message to console.
     * When debug mode is disabled (environment variable not available or not set to 1, don't echo message.
     * @param mixed $message
     */
    protected function debug($message)
    {
        $debugMode = getenv(self::DEBUG_MODE);
        if (1 == $debugMode) {
            echo $message . PHP_EOL;
        }
    }
}
