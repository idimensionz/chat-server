<?php

namespace Tests;

use iDimensionz\ChatServer\ChatMessage;
use PHPUnit\Framework\TestCase;

class ChatMessageUnitTest extends TestCase
{
    /**
     * @var ChatMessageTestStub
     */
    private $chatMessage;

    public function setUp()
    {
        parent::setUp();
        $this->chatMessage = new ChatMessageTestStub();
    }

    public function testConstants()
    {
        $this->assertSame('text', ChatMessage::MESSAGE_TYPE_TEXT);
    }

    public function testMessageGetterAndSetter()
    {
        $expectedValue = 'Test message';
        $this->chatMessage->setMessage($expectedValue);
        $actualValue = $this->chatMessage->getMessage();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSentDateGetterAndSetter()
    {
        $expectedValue = new \DateTime();
        $this->chatMessage->setSentDate($expectedValue);
        $actualValue = $this->chatMessage->getSentDate();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testGetMessageTypes()
    {
        $actualValue = $this->chatMessage->getValidMessageTypes();
        $this->assertIsArray($actualValue);
        $this->assertSame(1, count($actualValue));
        $this->assertTrue(in_array('text', $actualValue));
    }

    public function testIsValidMessageTypeWhenMessageTypeIsValid()
    {
        $actualValue = $this->chatMessage->isValidMessageType(ChatMessage::MESSAGE_TYPE_TEXT);
        $this->assertTrue($actualValue);
    }

    public function testIsValidMessageTypeWhenMessageTypeIsInvalid()
    {
        $actualValue = $this->chatMessage->isValidMessageType('invalid message type');
        $this->assertFalse($actualValue);
    }

    public function testMessageTypeGetterAndSetterWhenMessageTypeIsValid()
    {
        $expectedValue = ChatMessage::MESSAGE_TYPE_TEXT;
        $this->chatMessage->setMessageType($expectedValue);
        $actualValue = $this->chatMessage->getMessageType();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testMessageTypeGetterAndSetterWhenMessageTypeIsNotValid()
    {
        $expectedValue = ChatMessage::MESSAGE_TYPE_TEXT;
        $this->chatMessage->setMessageType('invalid message type');
        $actualValue = $this->chatMessage->getMessageType();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSystemMessageGetterAndSetter()
    {
        $this->chatMessage->setIsSystemMessage(true);
        $actualValue = $this->chatMessage->isSystemMessage();
        $this->assertTrue($actualValue);

        $this->chatMessage->setIsSystemMessage(false);
        $actualValue = $this->chatMessage->isSystemMessage();
        $this->assertFalse($actualValue);
    }

    public function testConstructWhenNoMessageTypeProvided()
    {
        $this->assertNotEmpty($this->chatMessage->getSentDate());
        $this->assertSame(ChatMessage::MESSAGE_TYPE_TEXT, $this->chatMessage->getMessageType());
        $this->assertFalse($this->chatMessage->isSystemMessage());
    }

    public function testConstructWhenParameterProvided()
    {
        // Text is currently the only valid message type so message type will be text whether it is passed or not.
        $this->chatMessage = new ChatMessageTestStub(ChatMessage::MESSAGE_TYPE_TEXT);
        $this->assertNotEmpty($this->chatMessage->getSentDate());
        $this->assertSame(ChatMessage::MESSAGE_TYPE_TEXT, $this->chatMessage->getMessageType());
        $this->assertFalse($this->chatMessage->isSystemMessage());
    }

    public function testUserNameGetterAndSetter()
    {
        $expectedValue = 'Jim';
        $this->chatMessage->setUserName($expectedValue);
        $actualValue = $this->chatMessage->getUserName();
        $this->assertSame($expectedValue, $actualValue);
    }

    public function testJsonSerialize()
    {
        $expectedDate = new \DateTime();
        $expectedUserName = 'Jim';
        $expectedMessage = 'Test message';
        $this->chatMessage->setSentDate($expectedDate);
        $this->chatMessage->setUserName($expectedUserName);
        $this->chatMessage->setMessage($expectedMessage);
        $actualValue = $this->chatMessage->jsonSerialize();
        $this->assertIsArray($actualValue);
        $this->assertTrue(isset($actualValue['messageType']));
        $this->assertTrue(isset($actualValue['message']));
        $this->assertTrue(isset($actualValue['sentDate']));
        $this->assertTrue(isset($actualValue['isSystemMessage']));
        $this->assertTrue(isset($actualValue['userName']));
        $this->assertSame(ChatMessage::MESSAGE_TYPE_TEXT, $actualValue['messageType']);
        $this->assertIsString($actualValue['message']);
        $this->assertSame($expectedMessage, $actualValue['message']);
        $this->assertSame($expectedDate->format('Y-m-d h:i:s a'), $actualValue['sentDate']);
        $this->assertFalse($actualValue['isSystemMessage']);
        $this->assertSame($expectedUserName, $actualValue['userName']);
    }
}
