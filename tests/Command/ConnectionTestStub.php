<?php

namespace Tests\Command;

use Ratchet\ConnectionInterface;

/**
 * Use this class to create mock Connection objects for unit tests.
 * Class ConnectionTestStub
 * @package Tests\Command
 */
class ConnectionTestStub implements ConnectionInterface
{
    const VALID_RESOURCE_ID = 27;
    const VALID_USERNAME = 'Ima TestUser';

    /**
     * @var int
     */
    public $resourceId;
    /**
     * @var string
     */
    public $username;

    public function __construct()
    {
        $this->resourceId = self::VALID_RESOURCE_ID;
        $this->username = self::VALID_USERNAME;
    }

    /**
     * @inheritDoc
     */
    function send($data)
    {
        // TODO: Implement send() method.
    }

    /**
     * @inheritDoc
     */
    function close()
    {
        // TODO: Implement close() method.
    }
}
