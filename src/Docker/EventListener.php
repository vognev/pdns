<?php

namespace PDNS\Docker;

use PDNS\Docker\Communication\SocketStateMachine;

class EventListener extends SocketStateMachine
{
    protected ?\Closure $eventCallback = null;

    function setEventCallback(\Closure $eventCallback) : self
    {
        $this->eventCallback = $eventCallback;

        return $this;
    }

    function handleEvent(array $event)
    {
        $this->eventCallback && ($this->eventCallback)($event);
    }

    function loop()
    {
        $this->setState(new EventListener\SendRequest($this));

        while (true) {
            $this->communicate();
        }
    }
}
