<?php

namespace PDNS\Docker\EventListener;

use PDNS\Docker\Communication\IRecvState;
use PDNS\Docker\EventListener;

class ParseEvent implements IRecvState
{
    protected const MAX_CHUNK_SIZE_LEN = 8;

    protected ?int $chunkSize = null;

    protected string $buffer = "";

    public function __construct(
        protected EventListener $listener,
        protected array $headers,
    ) { }

    function recv(\Socket $socket)
    {
        $consumed = 0;

        if (is_null($this->chunkSize)) {
            if (false === (socket_recv($socket, $chunk, self::MAX_CHUNK_SIZE_LEN, MSG_PEEK))) {
                throw new \RuntimeException(socket_strerror(
                    socket_last_error($socket)
                ));
            }

            if (strlen($chunk) >= self::MAX_CHUNK_SIZE_LEN && !str_contains($chunk, "\r\n")) {
                throw new \RuntimeException("Too long chunk size line received");
            }

            if (false !== ($crlf = strpos($chunk, "\r\n"))) {
                $this->chunkSize = hexdec(substr($chunk, 0, $crlf));
                $consumed = $crlf + 2;
            }
        } else {
            $reminder = $this->chunkSize + 2 - strlen($this->buffer);

            if (false === (socket_recv($socket, $chunk, $reminder, MSG_PEEK))) {
                throw new \RuntimeException(socket_strerror(
                    socket_last_error($socket)
                ));
            }

            $consumed = strlen($chunk);
            $this->buffer .= $chunk;

            if ($reminder === strlen($chunk)) {
                $message = json_decode($this->buffer, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new \RuntimeException(json_last_error_msg());
                }

                $this->listener->handleEvent($message);
                $this->listener->setState(new self(
                    $this->listener, $this->headers
                ));
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
