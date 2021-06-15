<?php

namespace PDNS\Docker\EngineClient;

use PDNS\Docker\EngineClient;

class ParseResponse
{
    protected const STATUS_RE = '@^HTTP/(1\.[0|1]) (?P<status>[1-5][0-9]{2}) @';

    protected array $response;

    protected int $status;

    function __construct(
        protected EngineClient $query,
        protected array        $headers,
        string                 $message
    ) {
        if (! count($this->headers) || ! preg_match(self::STATUS_RE, $this->headers[0], $matches)) {
            throw new \RuntimeException('Malformed headers');
        }

        $this->status = (int) $matches['status'];

        $contentType = null;

        foreach ($this->headers as $line) {
            if (0 === stripos($line, 'Content-Type:')) {
                [, $contentType] = explode(':', $line, 2);
                $contentType = trim($contentType);
            }
        }

        if (is_null($contentType) || 'application/json' === strtolower($contentType)) {
            $this->response = json_decode($message, !0);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException(json_last_error_msg());
            }
        } else {
            throw new \RuntimeException('Unknown or unsupported content type');
        }
    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function getResponse() : array
    {
        return $this->response;
    }
}
