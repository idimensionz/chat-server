<?php

namespace Tests;

use Ratchet\ConnectionInterface;

class ConnectionTestStub implements ConnectionInterface
{
    /**
     * @var int
     */
    public $resourceId = 123;
    /**
     * @var string
     */
    public $username = '';

    public function __get($name)
    {
        return $this->$name;
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
