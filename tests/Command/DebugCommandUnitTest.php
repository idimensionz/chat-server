<?php

namespace Tests\Command;

use iDimensionz\ChatServer\Command\DebugCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

class DebugCommandUnitTest extends TestCase
{
    use CommandUnitTestTrait;

    /**
     * @var DebugCommand
     */
    private $debugCommand;

    public function setUp()
    {
        parent::setUp();
        $this->hasMockChatServer();
        $this->debugCommand = new DebugCommand($this->mockChatServer);
    }

    public function tearDown()
    {
        unset($this->mockChatServer);
        unset($this->debugCommand);
        parent::tearDown();
    }

    public function testConstruct()
    {
        $expectedDescription = 'Dumps messages to the chat server console. Only useful for developers.';
        $actualDescription = $this->debugCommand->getDescription();
        $this->assertSame($expectedDescription, $actualDescription);

        $expectedHelp = 'Usage: "/debug <debug type>"' . PHP_EOL . 'Example "/debug ' . DebugCommand::DEBUG_MESSAGES . '" would dump the current list of JSON encoded messages to the chat server console.';
        $actualHelp = $this->debugCommand->getHelp();
        $this->assertSame($expectedHelp, $actualHelp);
    }

    public function testExecuteWhenParameterIsMessages()
    {
        $parameter = DebugCommand::DEBUG_MESSAGES;
        $mockConnection = \Phake::mock(ConnectionInterface::class);
        $this->debugCommand->execute($mockConnection, $parameter);
        /**
         * @var WebSocketChatServer $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockChatServer, \Phake::times(1));
        $verifierProxy->debug(\Phake::anyParameters());
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($mockConnection, \Phake::times(1));
        $verifierProxy->send('Debug-' . DebugCommand::DEBUG_MESSAGES . ': completed successfully.');
    }

    public function testExecuteWhenParameterNotRecognized()
    {
        $parameter = 'invalid';
        $mockConnection = \Phake::mock(ConnectionInterface::class);
        $this->debugCommand->execute($mockConnection, $parameter);
        /**
         * @var WebSocketChatServer $verifierProxy
         */
        $verifierProxy = \Phake::verify($this->mockChatServer, \Phake::times(0));
        $verifierProxy->debug(\Phake::anyParameters());
        /**
         * @var ConnectionInterface $verifierProxy
         */
        $verifierProxy = \Phake::verify($mockConnection, \Phake::times(0));
        $verifierProxy->send('Debug-' . DebugCommand::DEBUG_MESSAGES . ': completed successfully.');
    }
}
