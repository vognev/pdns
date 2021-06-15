<?php

namespace PDNS\DNS\Proto;

use PDNS\Binary\BinaryReader;
use PDNS\Binary\BinaryWriter;

class Question
{
    public function __construct(
        private QName $qname,
        private QType $qtype,
        private QClass $qclass
    ) {}

    public function write(BinaryWriter $writer)
    {
        $this->qname->write($writer);

        $writer->write_int16($this->qtype->value);
        $writer->write_int16($this->qclass->value);
    }

    public static function read(BinaryReader $reader) : Question
    {
        return new Question(
            QName::read($reader),
            QType::from($reader->read_int16()),
            QClass::from($reader->read_int16()),
        );
    }
}
