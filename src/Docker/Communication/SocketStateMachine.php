<?php

namespace PDNS\Docker\Communication;

abstract class SocketStateMachine
{
    protected mixed $state;

    public function __construct(
        protected \Socket $socket
    ) { }

    public function setState(mixed $state)
    {
        $this->state = $state;
    }

    public function communicate()
    {
        if (! is_a($this->state, ISendState::class) && ! is_a($this->state, IRecvState::class)) {
            return;
        }

        $r = $this->state instanceof IRecvState ? [$this->socket] : [];
        $w = $this->state instanceof ISendState ? [$this->socket] : [];

        $e = ($r or $w) ? [$this->socket] : [];

        if (false === socket_select($r, $w, $e, 5)) {
            throw new \RuntimeException('socket_select error: ' . socket_last_error());
        }

        count($e) && array_walk($e, function(\Socket $s) {
            throw new \RuntimeException('socket error: ' . socket_last_error($s));
        });

        count($w) && array_walk($w, function(\Socket $s) {
            $this->state->send($s);
        });

        count($r) && array_walk($r, function(\Socket $s) {
            $this->state->recv($s);
        });
    }
}
