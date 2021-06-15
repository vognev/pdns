<?php

namespace PDNS\Docker\EventListener;

use PDNS\Docker\Communication\ISendState;
use PDNS\Docker\EventListener;

class SendRequest implements ISendState
{
    protected string $buffer;

    public function __construct(
        protected EventListener $listener,
    ) {
        $this->buffer = implode("\r\n", [
            "GET /v1.35/events?filters=" . (json_encode(['type' => ['container'], 'event' => ['start', 'die']])) . " HTTP/1.1",
            "Host: localhost",
            "\r\n"
        ]);
    }

    function send(\Socket $socket)
    {
        if (false === ($len = socket_write($socket, $this->buffer))) {
            throw new \RuntimeException(socket_strerror(socket_last_error($socket)));
        }

        $this->buffer = substr($this->buffer, $len);

        if (! strlen($this->buffer)) {
            $this->listener->setState(
                new EventListener\ReadHeaders($this->listener)
            );
        }
    }
}
