<?php

namespace Tests\Command;

use iDimensionz\ChatServer\ChatMessage;
use iDimensionz\ChatServer\WebSocketChatServer;

/**
 * Use this trait to get common testing features for commands.
 *
 * Class CommandUnitTestTrait
 * @package Tests\Command
 */
trait CommandUnitTestTrait
{
    /**
     * @var ConnectionTestStub
     */
    protected $mockConnection;
    /**
     * @var WebSocketChatServer|\Phake_IMock
     */
    protected $mockChatServer;
    /**
     * @var \SplObjectStorage
     */
    protected $mockClients;

    protected function hasMockConnection($name = null)
    {
        $this->mockConnection = new ConnectionTestStub();
    }

    /**
     * @param $message
     * @param $encodedChatMessage
     */
    protected function hasMockChatServer($message = null, $encodedChatMessage = null): void
    {
        $message = $message ?? 'Some message';
        $this->mockChatServer = \Phake::mock(WebSocketChatServer::class);
        \Phake::when($this->mockChatServer)->createEncodedSystemChatMessage($message)
            ->thenReturn($encodedChatMessage);
    }

    protected function hasEncodedSystemChatMessage($message = null)
    {
        $message = $message ?? 'Hello world!';
        $chatMessage = \Phake::mock(ChatMessage::class);
        $chatMessage->setIsSystemMessage(true);
        $chatMessage->setMessage($message);
        $chatMessage->setUserName(WebSocketChatServer::USER_NAME_SYSTEM);

        return json_encode($chatMessage);
    }

    /**
     * @param int $clientCount
     */
    private function hasClients($clientCount=5)
    {
        $this->mockClients = new \SplObjectStorage();
        $validResourceId = ConnectionTestStub::VALID_RESOURCE_ID;
        for ($i=1;$i<=$clientCount;$i++) {
            $mockClient = new ConnectionTestStub();
            $mockClient->resourceId = $validResourceId + $i;
            $mockClient->username = 'User ' . $validResourceId;
            $this->mockClients->attach($mockClient);
        }
        $this->mockClients->attach($this->mockConnection);
        $this->mockClients->rewind();
        $this->mockChatServer->setClients($this->mockClients);
    }
}
