<?php

namespace PDNS\DNS\Proto;

use PDNS\Support\BinaryReader;
use PDNS\Support\BinaryWriter;

class Record
{
    public function __construct(
        protected QName $qname,
        protected QType $qtype,
        protected QClass $qclass,
        protected int $ttl,
        protected string $rdata,
    ) {}

    public static function read(BinaryReader $reader) : Record
    {
        $qname = QName::read($reader);

        $qtype = new QType($reader->read_int16());
        $class = new QClass($reader->read_int16());
        $ttl = $reader->read_int32();

        $rdata_len = $reader->read_int16();
        $rdata = $reader->read_data($rdata_len);

        return new Record($qname, $qtype, $class, $ttl, $rdata);
    }

    public function write(BinaryWriter $writer)
    {
        $this->qname->write($writer);

        $writer->write_int16($this->qtype->value());
        $writer->write_int16($this->qclass->value());
        $writer->write_int32($this->ttl);

        $writer->write_int16(strlen($this->rdata));
        $writer->write_data($this->rdata);
    }
}
