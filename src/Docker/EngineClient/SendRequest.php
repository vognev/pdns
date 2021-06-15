<?php

namespace PDNS\Docker\EngineClient;

use PDNS\Docker\Communication\ISendState;
use PDNS\Docker\EngineClient;

class SendRequest implements ISendState
{
    public function __construct(
        protected EngineClient $client,
        protected string $buffer,
    ) { }

    function send(\Socket $socket)
    {
        if (false === ($len = socket_write($socket, $this->buffer))) {
            throw new \RuntimeException(socket_strerror(socket_last_error($socket)));
        }

        $this->buffer = substr($this->buffer, $len);

        if (! strlen($this->buffer)) {
            $this->client->setState(
                new EngineClient\ReadHeaders($this->client)
            );
        }
    }
}
