<?php

namespace iDimensionz\ChatServer;

class ChatMessage implements \JsonSerializable
{
    const MESSAGE_TYPE_TEXT = 'text';

    /**
     * @var mixed
     */
    private $message;
    /**
     * @var \DateTime
     */
    private $sentDate;
    /**
     * @var string
     */
    private $messageType;
    /**
     * @var bool
     */
    private $isSystemMessage;
    /**
     * @var string
     */
    private $userName;
    /**
     * @var array
     */
    private $validMessageTypes;

    public function __construct(?string $messageType = null)
    {
        $this->setSentDate(new \DateTime());
        $messageType = $messageType ?? self::MESSAGE_TYPE_TEXT;
        $this->setMessageType($messageType);
        $this->setIsSystemMessage(false);
    }

    /**
     * @return mixed
     */
    protected function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return \DateTime
     */
    protected function getSentDate(): \DateTime
    {
        return $this->sentDate;
    }

    /**
     * @param \DateTime $sentDate
     */
    public function setSentDate(\DateTime $sentDate): void
    {
        $this->sentDate = $sentDate;
    }

    /**
     * @return string
     */
    protected function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     */
    public function setMessageType($messageType): void
    {
        if ($this->isValidMessageType($messageType)) {
            $this->messageType = $messageType;
        }
    }

    /**
     * @return bool
     */
    protected function isSystemMessage(): bool
    {
        return $this->isSystemMessage;
    }

    /**
     * @param bool $isSystemMessage
     */
    public function setIsSystemMessage(bool $isSystemMessage): void
    {
        $this->isSystemMessage = $isSystemMessage;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return array
     */
    public function getValidMessageTypes(): array
    {
        return [
            self::MESSAGE_TYPE_TEXT,
        ];
    }

    /**
     * @param string $messageType
     * @return bool
     */
    public function isValidMessageType(string $messageType)
    {
        return in_array($messageType, $this->getValidMessageTypes());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        switch ($this->getMessageType()) {
            case self::MESSAGE_TYPE_TEXT:
                $message = (string) $this->getMessage();
                break;
        }

        return [
            'messageType' => $this->getMessageType(),
            'message' => $message,
            'sentDate' => $this->getSentDate()->format('Y-m-d h:i:s a'),
            'isSystemMessage' => $this->isSystemMessage,
            'userName' => $this->getUserName(),
        ];
    }
}
