<?php

namespace PDNS\Support;

class BinaryReader
{
    protected int $position;

    public function __construct(protected string $buffer)
    {
        $this->position = 0;
    }

    function seek($position) : int
    {
        assert($position < strlen($this->buffer));

        try {
            return $this->position;
        } finally {
            $this->position = $position;
        }
    }

    function read_data(int $length) : string
    {
        assert($this->position + $length < strlen($this->buffer));

        try {
            return substr($this->buffer, $this->position, $length);
        } finally {
            $this->position += $length;
        }
    }

    function read_int32() : int
    {
        assert($this->position + 4 < strlen($this->buffer));

        $byte0 = ord($this->buffer[$this->position++]);
        $byte1 = ord($this->buffer[$this->position++]);
        $byte2 = ord($this->buffer[$this->position++]);
        $byte3 = ord($this->buffer[$this->position++]);

        return ($byte0 << 24) | ($byte1 << 16) | ($byte2 << 8) | $byte3;
    }

    function read_int16() : int
    {
        assert($this->position + 2 < strlen($this->buffer));

        $byte0 = ord($this->buffer[$this->position++]);
        $byte1 = ord($this->buffer[$this->position++]);

        return ($byte0 << 8) | $byte1;
    }

    function read_int8() : int
    {
        assert($this->position + 1 < strlen($this->buffer));

        return ord($this->buffer[$this->position++]);
    }
}
