<?php

namespace Tests\Command;

use iDimensionz\ChatServer\Command\NameCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

class NameCommandUnitTest extends TestCase
{
    use CommandUnitTestTrait;

    /**
     * @var NameCommand
     */
    private $nameCommand;

    public function setUp()
    {
        parent::setUp();
        $this->hasMockChatServer();
        $this->nameCommand = new NameCommand($this->mockChatServer);
    }

    public function tearDown()
    {
        unset($this->mockClients);
        unset($this->mockConnection);
        unset($this->mockChatServer);
        unset($this->nameCommand);
        parent::tearDown();
    }

    public function testConstruct()
    {
        $this->assertSame(
            'Identifies your messages by your name. Your name will be displayed to other users in each message you send.',
            $this->nameCommand->getDescription()
        );

        $this->assertSame(
            'Usage: "/name <your name>"' . PHP_EOL . 'Example "/name Jim" would set your name to Jim',
            $this->nameCommand->getHelp()
        );
    }

    public function testExecute()
    {
        $commandParameter = 'Dude';
        $this->hasMockConnection();
        $previousUserName = $this->mockConnection->username;
        $message = "Connection {$previousUserName} is now known as '{$commandParameter}'";
        $encodedChatMessage = $this->hasEncodedSystemChatMessage($message);
        \Phake::when($this->mockChatServer)->createEncodedSystemChatMessage($message)
            ->thenReturn($encodedChatMessage);
        $this->hasClients();

        $this->nameCommand->execute($this->mockConnection, $commandParameter);

        /**
         * @var WebSocketChatServer $proxyVerifier
         */
//        \Phake::verify($this->mockChatServer, \Phake::times(1))
        $proxyVerifier = \Phake::verify($this->mockChatServer, \Phake::times(1));
        $proxyVerifier->debug("Name set to '{$commandParameter}' for connection {$this->mockConnection->resourceId}");
        $proxyVerifier->distributeEncodedChatMessage($this->mockConnection, $encodedChatMessage, false);
        $proxyVerifier->getClients();
        $proxyVerifier->updateUserNameInMessages($previousUserName, $commandParameter);
    }
}
