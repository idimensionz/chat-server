<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    const COMMAND_PREFIX = '/';

    /**
     * @var \SplObjectStorage
     */
    private $clients;
    /**
     * @var array
     */
    private $messages;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->messages = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})".PHP_EOL;
        foreach ($this->messages as $message) {
            $conn->send($message);
        }
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $message, $numRecv, $numRecv == 1 ? '' : 's');
        if (self::COMMAND_PREFIX == substr($message, 0, 1)) {
            $this->processCommand($from, $message);
        } else {
            $chatMessage = new ChatMessage();
            $chatMessage->setMessage($message);
            $encodedChatMessage = json_encode($chatMessage);
            $this->messages[] = $encodedChatMessage;
            /**
             * @var ConnectionInterface $client
             */
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($encodedChatMessage);
                }
            }
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
     */
    private function processCommand(ConnectionInterface $from, $message)
    {
        echo "Processing command '{$message}'".PHP_EOL;
        $pieces = explode(' ', $message);
        echo print_r($pieces, true);
        switch (trim($pieces[0])) {
            case '/name':
                unset($pieces[0]);
                $name = implode(' ', $pieces);
                echo "Name set to '{$name}' for connection {$from->resourceId}";
                $from->username = $name;
                $from->send("Your name is set to '{$from->username}'");
                $this->clients->offsetSet($from);
                break;
        }
    }
}
