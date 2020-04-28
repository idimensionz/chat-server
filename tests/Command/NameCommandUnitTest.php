<?php

namespace Tests\Command;

use iDimensionz\ChatServer\Command\NameCommand;
use iDimensionz\ChatServer\WebSocketChatServer;
use PHPUnit\Framework\TestCase;

class NameCommandUnitTest extends TestCase
{
    /**
     * @var NameCommand
     */
    private $nameCommand;
    /**
     * @var WebSocketChatServer
     */
    private $mockChatServer;

    public function setUp()
    {
        parent::setUp();
        $this->mockChatServer = \Phake::mock(WebSocketChatServer::class);
        $this->nameCommand = new NameCommand($this->mockChatServer);
    }

    public function tearDown()
    {
        unset($this->mockChatServer);
        unset($this->nameCommand);
        parent::tearDown();
    }

    public function test__construct()
    {

    }

    public function testExecute()
    {

    }
}
