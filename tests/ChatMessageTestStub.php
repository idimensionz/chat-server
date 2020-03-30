<?php

namespace Tests;

use iDimensionz\ChatServer\ChatMessage;

class ChatMessageTestStub extends ChatMessage
{
    public function getMessage()
    {
        return parent::getMessage();
    }

    public function getSentDate(): \DateTime
    {
        return parent::getSentDate();
    }

    public function getMessageType(): string
    {
        return parent::getMessageType();
    }

    public function isSystemMessage(): bool
    {
        return parent::isSystemMessage();
    }
}
