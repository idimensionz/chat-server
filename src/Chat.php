<?php
namespace MyApp;
use MyApp\Command\NameCommand;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    const COMMAND_PREFIX = '/';
    const USER_NAME_SYSTEM = 'Chat Server';

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

    public function onOpen(ConnectionInterface $conn)
    {
        $conn->username = "id {$conn->resourceId}";
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})".PHP_EOL;
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
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $message, $numRecv, $numRecv == 1 ? '' : 's');
        if (self::COMMAND_PREFIX == substr($message, 0, 1)) {
            $this->processCommand($from, $message);
        } else {
            $encodedChatMessage = $this->createEncodedChatMessage($from, $message);
echo $encodedChatMessage . PHP_EOL;
            $this->messages[] = $encodedChatMessage;
            $this->distributeEncodedChatMessage($from, $encodedChatMessage);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected".PHP_EOL;
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}".PHP_EOL;

        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param $message
     * @throws \Exception
     */
    private function processCommand(ConnectionInterface $from, $message)
    {
        echo "Processing command message: '{$message}'".PHP_EOL;
        $pieces = explode(' ', $message);
        $command = explode('/', $pieces[0])[1];
        unset($pieces[0]);
        $commandParameter = implode(' ', $pieces);
        if (isset($this->availableCommands[$command])) {
            $this->availableCommands[$command]->execute($from, $commandParameter);
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
     * @param ConnectionInterface $from
     * @param string $encodedChatMessage
     * @param bool $skipSender
     */
    public function distributeEncodedChatMessage(
        ConnectionInterface $from,
        string $encodedChatMessage,
        bool $skipSender = true
    ): void {
        /**
         * @var ConnectionInterface $client
         */
        foreach ($this->clients as $client) {
            if (!$skipSender || $from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($encodedChatMessage);
            }
        }
    }


    /**
     * @param ConnectionInterface $from
     * @return string
     */
    protected function getClientUserName(ConnectionInterface $from)
    {
        $clientUserName = '';
        foreach ($this->clients as $client) {
            echo $client->resourceId . PHP_EOL;
            if ($from == $client) {
                echo "Found match!" . PHP_EOL;
                //                echo __METHOD__ . print_r($client, true) . PHP_EOL;
                $clientUserName = $client->username;
                echo "Match's username: {$clientUserName}" . PHP_EOL;
            }
        }
        //        $client = $this->clients->offsetGet($from);
        return $clientUserName;
    }

    protected function registerCommands()
    {
        // @todo Iterate through the classes in Command dir and register each class that implements CommandInterface.
        $this->availableCommands[NameCommand::getCommandName()] = new NameCommand($this);
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
}
