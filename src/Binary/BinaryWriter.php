<?php

namespace PDNS\Binary;

class BinaryWriter
{
    protected int $position;

    public function __construct(protected string $buffer = '')
    {
        $this->position = strlen($this->buffer);
    }

    public function write_data(string $data)
    {
        $this->buffer .= $data;
        $this->position += strlen($data);
    }

    public function write_int32(int $val)
    {
        $b0 = (($val & 0xff << 0) >> 0);
        $b1 = (($val & 0xff << 8) >> 8);
        $b2 = (($val & 0xff << 16) >> 16);
        $b3 = (($val & 0xff << 24) >> 24);

        $this->buffer .= chr($b3);
        $this->buffer .= chr($b2);
        $this->buffer .= chr($b1);
        $this->buffer .= chr($b0);

        $this->position += 4;
    }

    public function write_int16(int $val)
    {
        $b0 = (($val & 0xff << 0) >> 0);
        $b1 = (($val & 0xff << 8) >> 8);
        $this->buffer .= chr($b1);
        $this->buffer .= chr($b0);
        $this->position += 2;
    }

    public function write_int8(int $val)
    {
        $this->buffer .= chr($val & 0xff);
        $this->position +=1;
    }

    public function buffer() : string
    {
        return $this->buffer;
    }
}
