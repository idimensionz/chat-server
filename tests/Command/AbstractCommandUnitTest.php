<?php

namespace Tests\Command;

use iDimensionz\ChatServer\Command\AbstractCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use PHPUnit\Framework\TestCase;
use Tests\CommandTestStub;

class AbstractCommandUnitTest extends TestCase
{
    /**
     * @var CommandTestStub
     */
    private $commandTestStub;
    /**
     * @var WebSocketChatServer
     */
    private $mockChatServer;

    public function setUp()
    {
        parent::setUp();
        $this->mockChatServer = \Phake::mock(WebSocketChatServer::class);
        $this->commandTestStub = new CommandTestStub($this->mockChatServer);
    }

    public function tearDown()
    {
        unset($this->mockChatServer);
        unset($this->commandTestStub);
        parent::tearDown();
    }

    public function testChatServerGetterAndSetter()
    {
        $mockChatServer = \Phake::mock(WebSocketChatServer::class);
        $this->commandTestStub->setChatServer($mockChatServer);
        $actualChatServer = $this->commandTestStub->getChatServer();
        $this->assertInstanceOf(WebSocketChatServer::class, $actualChatServer);
        $this->assertInstanceOf(\Phake_IMock::class, $actualChatServer);
        $this->assertSame($mockChatServer, $actualChatServer);
    }

    public function testConstruct()
    {
        $actualChatServer = $this->commandTestStub->getChatServer();
        $this->assertInstanceOf(WebSocketChatServer::class, $actualChatServer);
        $this->assertInstanceOf(\Phake_IMock::class, $actualChatServer);
        $this->assertSame($this->mockChatServer, $actualChatServer);
    }

    public function testGetCommandName()
    {
        $actualValue = CommandTestStub::getCommandName();
        $this->assertSame(CommandTestStub::$commandName, $actualValue);
    }

    public function testHelpGetterAndSetter()
    {
        $expectedValue = 'Some help text';
        $this->commandTestStub->setHelp($expectedValue);
        $actualValue = $this->commandTestStub->getHelp();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testDescriptionGetterAndSetter()
    {
        $expectedValue = 'Some description';
        $this->commandTestStub->setDescription($expectedValue);
        $actualValue = $this->commandTestStub->getDescription();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testToString()
    {

    }
}
