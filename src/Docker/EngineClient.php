<?php

namespace PDNS\Docker;

use PDNS\Docker\Communication\IRecvState;
use PDNS\Docker\Communication\ISendState;
use PDNS\Docker\Communication\SocketStateMachine;

class EngineClient extends SocketStateMachine
{
    public function getContainers() : array
    {
        $this->setState(new EngineClient\SendRequest($this, implode("\r\n", [
            "GET /containers/json HTTP/1.1",
            "Host: localhost",
            "\r\n"
        ])));

        while (true) {
            if (
                is_a($this->state, ISendState::class) ||
                is_a($this->state, IRecvState::class)
            ) {
                $this->communicate();
                continue;
            }

            if ($this->state instanceof EngineClient\ParseResponse) {
                return $this->state->getResponse();
            } else {
                throw new \RuntimeException('Unexpected state');
            }
        }
    }

    public function getContainer(string $id) : array
    {
        $this->setState(new EngineClient\SendRequest($this, implode("\r\n", [
            "GET /containers/${id}/json HTTP/1.1",
            "Host: localhost",
            "\r\n"
        ])));

        while (true) {
            if (
                is_a($this->state, ISendState::class) ||
                is_a($this->state, IRecvState::class)
            ) {
                $this->communicate();
                continue;
            }

            if ($this->state instanceof EngineClient\ParseResponse) {
                return $this->state->getResponse();
            } else {
                throw new \RuntimeException('Unexpected state');
            }
        }
    }
}
