<?php

namespace PDNS\Docker\EventListener;

use PDNS\Docker\Communication\IRecvState;
use PDNS\Docker\EventListener;

class ReadHeaders implements IRecvState
{
    protected const MAX_HEADER_SIZE = 8192;

    protected array $headers = [];

    public function __construct(
        protected EventListener $listener,
    ) { }

    function recv(\Socket $socket)
    {
        if (false === (socket_recv($socket, $chunk, self::MAX_HEADER_SIZE, MSG_PEEK))) {
            throw new \RuntimeException(socket_strerror(
                socket_last_error($socket)
            ));
        }

        if (strlen($chunk) >= self::MAX_HEADER_SIZE && !str_contains($chunk, "\r\n")) {
            throw new \RuntimeException("Too long header line received");
        }

        $consumed = 0;

        while ($crlf = strpos($chunk, "\r\n", $consumed)) {
            $line = substr($chunk, $consumed, $crlf + 2 - $consumed);
            $consumed = $crlf + 2;

            if ("\r\n" === $line) {
                $this->listener->setState(new EventListener\ParseEvent(
                    $this->listener, $this->headers
                ));
                break;
            } else {
                $this->headers[] = trim($line);
            }
        }

        while ($consumed) {
            if (false === ($size = socket_recv($socket, $dummy, $consumed, 0))) {
                throw new \RuntimeException(socket_strerror(
                    socket_last_error($socket)
                ));
            }

            $consumed -= $size;
        }
    }
}
