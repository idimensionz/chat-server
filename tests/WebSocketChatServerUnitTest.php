<?php

namespace Tests;

use iDimensionz\ChatServer\ChatMessage;
use iDimensionz\ChatServer\Command\CommandInterface;
use iDimensionz\ChatServer\Command\DebugCommand;
use iDimensionz\ChatServer\Command\NameCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

class WebSocketChatServerUnitTest extends TestCase
{
    /**
     * @var WebSocketChatServerTestStub
     */
    private $webSocketChatServer;
    /**
     * @var \SplObjectStorage
     */
    private $clients;
    private $mockClient;

    public function setUp()
    {
        parent::setUp();
        $this->webSocketChatServer = new WebSocketChatServerTestStub();
    }

    public function tearDown()
    {
        unset($this->clients);
        unset($this->webSocketChatServer);
        parent::tearDown();
    }

    public function testConstants()
    {
        $this->assertSame('/', WebSocketChatServer::COMMAND_PREFIX);
        $this->assertSame('Chat Server', WebSocketChatServer::USER_NAME_SYSTEM);
    }

    public function testAvailableCommandGetterAndSetter()
    {
        $validArray = ['arbitrary value 1', 'arbitrary value 2'];
        $this->webSocketChatServer->setAvailableCommands($validArray);
        $actualValue = $this->webSocketChatServer->getAvailableCommands();
        $this->assertIsArray($actualValue);
        $this->assertSame($validArray, $actualValue);
    }

    public function testAddAvailableCommand()
    {
        // Clear out any commands added by the chat server.
        $this->webSocketChatServer->setAvailableCommands([]);
        $validMockCommandName = 'MockCommand';
        $mockCommand = \Phake::mock(CommandInterface::class);
        \Phake::whenStatic($mockCommand)->getCommandName()
            ->thenReturn($validMockCommandName);
        $this->webSocketChatServer->addAvailableCommand($mockCommand);
        $actualValue = $this->webSocketChatServer->getAvailableCommands();
        $this->assertIsArray($actualValue);
        $this->assertSame(1, count($actualValue));
        $this->assertTrue(isset($actualValue, $validMockCommandName));
        $this->assertInstanceOf(CommandInterface::class, $actualValue[$validMockCommandName]);
        $this->assertInstanceOf(\Phake_IMock::class, $actualValue[$validMockCommandName]);
    }

    public function testRegisterCommands()
    {
        // Clear out any command added during instantiation.
        $this->webSocketChatServer->setAvailableCommands([]);
        $this->webSocketChatServer->registerCommands();
        $this->assertAvailableCommands();
    }

    public function testClientsGetterAndSetter()
    {
        $mockClients = \Phake::mock(\SplObjectStorage::class);
        $this->webSocketChatServer->setClients($mockClients);
        $actualClients = $this->webSocketChatServer->getClients();
        $this->assertInstanceOf(\SplObjectStorage::class, $actualClients);
        $this->assertInstanceOf(\Phake_IMock::class, $actualClients);
    }

    public function testMessagesGetterAndSetter()
    {
        $validArray = ['arbitrary value 1', 'arbitrary value 2'];
        $this->webSocketChatServer->setMessages($validArray);
        $actualValue = $this->webSocketChatServer->getMessages();
        $this->assertIsArray($actualValue);
        $this->assertSame($validArray, $actualValue);
    }

    public function testConstruct()
    {
        // Validate clients
        $actualClients = $this->webSocketChatServer->getClients();
        $this->assertInstanceOf(\SplObjectStorage::class, $actualClients);
        $this->assertSame(0, $actualClients->count());
        // Validate messages
        $actualMessages = $this->webSocketChatServer->getMessages();
        $this->assertIsArray($actualMessages);
        $this->assertEmpty($actualMessages);
        // Validate registered commands
        $this->assertAvailableCommands();
    }

    public function testCreateEncodedSystemChatMessage()
    {
        $validMessage = 'This is a test message';
        $actualValue = $this->webSocketChatServer->createEncodedSystemChatMessage($validMessage);
        $this->assertIsString($actualValue);
        $actualArray = json_decode($actualValue, true);
        $this->assertTrue(isset($actualArray['messageType']));
        $this->assertSame(ChatMessage::MESSAGE_TYPE_TEXT, $actualArray['messageType']);
        $this->assertTrue(isset($actualArray['isSystemMessage']));
        $this->assertTrue($actualArray['isSystemMessage']);
        $this->assertTrue(isset($actualArray['message']));
        $this->assertSame($validMessage, $actualArray['message']);
        $this->assertTrue(isset($actualArray['userName']));
        $this->assertSame(WebSocketChatServer::USER_NAME_SYSTEM, $actualArray['userName']);
        $this->assertTrue(isset($actualArray['sentDate']));
    }

    public function testDistributeEncodedChatMessageSendsMessageToAllClientsWhenSkipSenderIsFalse()
    {
        $this->hasClients();
        $validMessage = 'some json encoded message';
        $actualClients = $this->webSocketChatServer->getClients();
        /**
         * @var ConnectionInterface $sender
         */
        $actualClients->rewind();
        $sender = $actualClients->current();
        $this->webSocketChatServer->distributeEncodedChatMessage($sender, $validMessage, false);
        \Phake::verify($this->mockClient, \Phake::times($this->webSocketChatServer->getClients()->count()))
            ->send($validMessage);
    }

    public function testDistributeEncodedChatMessageSendsMessageToAllClientsExceptSenderWhenSkipSenderIsTrue()
    {
        $this->hasClients();
        $validMessage = 'some json encoded message';
        $actualClients = $this->webSocketChatServer->getClients();
        /**
         * @var ConnectionInterface $sender
         */
        $actualClients->rewind();
        $sender = $actualClients->current();
        $this->webSocketChatServer->distributeEncodedChatMessage($sender, $validMessage);
        \Phake::verify($this->mockClient, \Phake::times($this->webSocketChatServer->getClients()->count() -1))
            ->send($validMessage);
    }

    public function testOnOpen()
    {
        $this->markTestIncomplete();
    }

    public function testOnClose()
    {
        $this->markTestIncomplete();
    }

    public function testOnError()
    {
        $this->markTestIncomplete();
    }

    public function testOnMessage()
    {
        $this->markTestIncomplete();
    }

    public function testCreateEncodedChatMessage()
    {
        $this->markTestIncomplete();
    }

    public function testUpdateUserNameInMessages()
    {
        $this->markTestIncomplete();
    }

    protected function assertAvailableCommands(): void
    {
        $actualCommands = $this->webSocketChatServer->getAvailableCommands();
        $this->assertIsArray($actualCommands);
        $this->assertSame(2, count($actualCommands));
        $this->assertTrue(isset($actualCommands[NameCommand::getCommandName()]));
        $this->assertInstanceOf(NameCommand::class, $actualCommands[NameCommand::getCommandName()]);
        $this->assertTrue(isset($actualCommands[DebugCommand::getCommandName()]));
        $this->assertInstanceOf(DebugCommand::class, $actualCommands[DebugCommand::getCommandName()]);
    }

    /**
     * @param int $clientCount
     */
    private function hasClients($clientCount=5)
    {
        $this->mockClient = \Phake::mock(ConnectionInterface::class);
        $mockClients = new \SplObjectStorage();
        for ($i=1;$i<=5;$i++) {
            $cloneClient = $this->mockClient;
            //$cloneClient->id = $i;
            $mockClients->attach($cloneClient);
        }
        $this->webSocketChatServer->setClients($mockClients);
    }
}
