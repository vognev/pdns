<?php

namespace PDNS\Docker\EngineClient;

use PDNS\Docker\Communication\IRecvState;
use PDNS\Docker\EngineClient;

class ReadResponse implements IRecvState
{
    protected const MAX_CHUNK_SIZE_LEN = 8;

    protected const NIL_CHUNK_SIZE_LEN = 7;

    protected ?int $chunkSize = null;

    protected ?int $contentSize = null;

    protected string $buffer = "";

    function __construct(
        protected EngineClient $query,
        protected array        $headers
    ) {
        foreach ($this->headers as $line) {
            if (0 === stripos($line, 'Content-Length:')) {
                [, $contentLength] = explode(':', $line, 2);
                $this->contentSize = (int) trim($contentLength);
                break;
            }
        }
    }

    function recv(\Socket $socket)
    {
        if (! is_null($this->contentSize)) {
            $this->recvContent($socket);
        } else {
            $this->recvChunked($socket);
        }
    }

    function recvContent(\Socket $socket)
    {
        $reminder = $this->contentSize - strlen($this->buffer);

        if (false === (socket_recv($socket, $chunk, $reminder, 0))) {
            throw new \RuntimeException(socket_strerror(
                socket_last_error($socket)
            ));
        }

        $this->buffer .= $chunk;

        if (0 === $reminder - strlen($chunk)) {
            $this->query->setState(new EngineClient\ParseResponse(
                $this->query, $this->headers, $this->buffer
            ));
        }
    }

    function recvChunked(\Socket $socket)
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
        } elseif (($reminder = $this->chunkSize + 2 - strlen($this->buffer)) === 0) {
            // read terminal zero-sized chunk
            if (false === (socket_recv($socket, $chunk, self::NIL_CHUNK_SIZE_LEN, MSG_PEEK))) {
                throw new \RuntimeException(socket_strerror(
                    socket_last_error($socket)
                ));
            }

            if ($chunk === "0\r\n\r\n") {
                $consumed = strlen($chunk);
                $this->query->setState(new EngineClient\ParseResponse(
                    $this->query, $this->headers, $this->buffer
                ));
            }
        } else {
            if (false === (socket_recv($socket, $chunk, $reminder, MSG_PEEK))) {
                throw new \RuntimeException(socket_strerror(
                    socket_last_error($socket)
                ));
            }

            $consumed = strlen($chunk);
            $this->buffer .= $chunk;
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
