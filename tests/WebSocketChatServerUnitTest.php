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
     * @var ConnectionInterface|\Phake_IMock
     */
    private $mockConnection;
    /**
     * @var int
     */
    private $validResourceId;
    /**
     * @var \SplObjectStorage
     */
    private $mockClients;
    private $validSentDate;

    public function setUp()
    {
        $this->validSentDate = (new \DateTime())->format('Y-m-d h:i:s a');
        $this->mockClients = new \SplObjectStorage();
        $this->validResourceId = 123;
        parent::setUp();
        $this->webSocketChatServer = new WebSocketChatServerTestStub();
    }

    public function tearDown()
    {
        unset($this->mockClients);
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
        $this->hasConnection();
        $validMessage = 'This is a test message';
        $actualValue = $this->hasEncodedChatMessage($this->mockConnection, $validMessage, true);
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
        $this->assertSame($this->validSentDate, $actualArray['sentDate']);
    }

    public function testDistributeEncodedChatMessageSendsMessageToAllClientsWhenSkipSenderIsFalse()
    {
        $skipSender = false;
        $this->hasConnection();
        $this->hasClients();
        $sender = $this->mockConnection;
        $validMessage = $this->hasEncodedChatMessage($sender, 'some message');
        $this->webSocketChatServer->distributeEncodedChatMessage($sender, $validMessage, $skipSender);
        $this->assertMessageSentToClients($validMessage, $skipSender);
    }

    public function testDistributeEncodedChatMessageSendsMessageToAllClientsExceptSenderWhenSkipSenderIsTrue()
    {
        $skipSender = true;
        $this->hasConnection();
        $this->hasClients();
        $sender = $this->mockConnection;
        $validMessage = $this->hasEncodedChatMessage($sender, 'some message');
        $this->webSocketChatServer->distributeEncodedChatMessage($sender, $validMessage, $skipSender);
        $this->assertMessageSentToClients($validMessage, $skipSender);
    }

    public function testOnOpenDoesNotSendMessageToConnectionWhenNoMessages()
    {
        $this->hasConnection();
        $this->webSocketChatServer->onOpen($this->mockConnection);
        $this->expectOutputString('New connection! (id 123)' . PHP_EOL);
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockConnection, \Phake::times(0));
        $verifierProxy->send(\Phake::anyParameters());
    }

    public function testOnOpenSendsMessagesToConnectionWhenMessagesExist()
    {
        $this->hasConnection();
        $validMessages = [
            'message 1',
            'message 2'
        ];
        $this->webSocketChatServer->setMessages($validMessages);
        $this->webSocketChatServer->onOpen($this->mockConnection);
        $this->expectOutputString('New connection! (id 123)' . PHP_EOL);
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockConnection, \Phake::times(count($validMessages)));
        $verifierProxy->send(\Phake::anyParameters());
    }

    /**
     * @throws \Exception
     */
    public function testProcessCommandWhenCommandIsNotRegistered()
    {
        $this->hasConnection();
        $validName = 'Ima Tester';
        $validMessage = sprintf('/%s %s', CommandTestStub::$commandName, $validName);
        $sender = $this->mockConnection;
        $this->webSocketChatServer->processCommand($sender, $validMessage);
        $this->expectOutputString("Processing command message: '{$validMessage}'".PHP_EOL);
        $expectedMessage = '{"messageType":"text","message":"\'\/test Ima Tester\' is not a valid command","sentDate":"'. $this->validSentDate . '","isSystemMessage":false,"userName":""}';
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockConnection, \Phake::times(1));
        $verifierProxy->send($expectedMessage);
    }

    /**
     * @throws \Exception
     */
    public function testProcessCommandWhenCommandIsRegistered()
    {
        $this->hasConnection();
        $this->webSocketChatServer->addAvailableCommand(new CommandTestStub($this->webSocketChatServer));
        $validName = 'Ima Tester';
        $validMessage = sprintf('/%s %s', CommandTestStub::$commandName, $validName);
        $sender = $this->mockConnection;
        $this->webSocketChatServer->processCommand($sender, $validMessage);
        $this->expectOutputString("Processing command message: '{$validMessage}'".PHP_EOL);
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockConnection, \Phake::times(1));
        $verifierProxy->send(CommandTestStub::TEST_OUTPUT);
    }

    /**
     * @throws \Exception
     */
    public function testOnMessageProcessesCommand()
    {
        $this->markTestSkipped();
    }

    /**
     * @throws \Exception
     */
    public function testOnMessageDistributesMessage()
    {
        $this->markTestSkipped('Already tested distributing messages.');
    }

    public function testOnClose()
    {
        $this->markTestIncomplete();
    }

    public function testOnError()
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

    /**
     * @param int $clientCount
     */
    private function hasClients($clientCount=5)
    {
        for ($i=1;$i<=$clientCount;$i++) {
            $mockClient = \Phake::mock(ConnectionTestStub::class);
            $mockClient->resourceId = $this->validResourceId + $i;
            $mockClient->username = 'User ' . $this->validResourceId;
            $this->mockClients->attach($mockClient);
        }
        $this->mockClients->attach($this->mockConnection);
        $this->mockClients->rewind();
        $this->webSocketChatServer->setClients($this->mockClients);
    }

    protected function hasConnection(): void
    {
        $this->mockConnection = \Phake::mock(ConnectionTestStub::class);
        $this->mockConnection->resourceId = $this->validResourceId;
        $this->mockConnection->username = 'User ' . $this->validResourceId;
    }

    /**
     * @param ConnectionInterface $mockConnection
     * @param string $message
     * @return false|string
     */
    private function hasEncodedChatMessage(ConnectionInterface $mockConnection, string $message, bool $isSystemEncodedChatMessage = false)
    {
        $userName = $isSystemEncodedChatMessage ? WebSocketChatServer::USER_NAME_SYSTEM : $mockConnection->username;
        return json_encode(
            [
                'messageType' => ChatMessage::MESSAGE_TYPE_TEXT,
                'message' => $message,
                'sentDate' => $this->validSentDate,
                'isSystemMessage' => $isSystemEncodedChatMessage,
                'userName' => $userName,
            ]
        );
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
     * @param string $validMessage
     * @param bool $skipSender
     */
    protected function assertMessageSentToClients(string $validMessage, $skipSender = true): void
    {
        /**
         * @var \Phake_IMock $mockClient
         */
        foreach ($this->mockClients as $mockClient) {
            if (!$skipSender || $this->mockConnection != $mockClient) {
                /**
                 * @var ConnectionInterface $verifierProxy
                 */
                $verifierProxy = \Phake::verify($mockClient, \Phake::times(1));
                $verifierProxy->send($validMessage);
            }
        }
        if (!$skipSender) {
            $verifierProxy = \Phake::verify($this->mockConnection, \Phake::times(1));
            $verifierProxy->send($validMessage);
        }
    }
}
