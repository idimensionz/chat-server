<?php

namespace Tests;

use Ratchet\ConnectionInterface;

class ConnectionTestStub implements ConnectionInterface
{
    public $resourceId = 123;

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
