<?php

namespace PDNS\DNS\Proto;

use PDNS\Support\BinaryReader;
use PDNS\Support\BinaryWriter;

class Header
{
    /**
     * @var int $id
     * u16, Packet Identifier
     * A random identifier is assigned to query packets
     */

    /**
     * @var int $qr
     * u8, Query Response
     * true for queries, false for responses
     */

    /**
     * @var int $opcode
     * u8, Operation Code
     * Typically always 0, see RFC1035 for details
     */

    /**
     * @var int $aa
     * u8, Authoritative Answer
     * Set to 1 if the responding server is authoritative - that is, it "owns" - the domain queried.
     */

    /**
     * @var int $tc
     * u8, Truncated Message
     * Set to 1 if the message length exceeds 512 bytes.
     * Traditionally a hint that the query can be reissued using TCP,
     * for which the length limitation doesn't apply.
     */

    /**
     * @var int $rd
     * u8, Recursion Desired
     * Set by the sender of the request if the server should attempt to resolve
     * the query recursively if it does not have an answer readily available.
     */

    /**
     * @var int $ra
     * u8, Recursion Available
     * Set by the server to indicate whether or not recursive queries are allowed.
     */

    /**
     * @var int $z
     * u8, Reserved
     * Originally reserved for later use, but now used for DNSSEC queries.
     */

    /**
     * @var int $rcode
     * u8, Response Code
     * Set by the server to indicate the status of the response, i.e. whether or not
     * it was successful or failed, and in the latter case providing details
     * about the cause of the failure.
     */

    function __construct(
        protected int $id,
        protected int $qr,
        protected int $opcode,
        protected int $aa,
        protected int $tc,
        protected int $rd,
        protected int $ra,
        protected int $z,
        protected int $rcode
    ) {

    }

    public function reply() : Header
    {
        return new Header(
            $this->id,
            1,
            $this->opcode,
            $this->aa,
            0,
            $this->rd,
            $this->ra,
            $this->z,
            0
        );
    }

    public function write(BinaryWriter $writer) : void
    {
        $writer->write_int16(
            $this->id
        );

        $writer->write_int8(
            ($this->qr << 7)
            | ($this->opcode << 3)
            | ($this->aa << 2)
            | ($this->tc << 1)
            | ($this->rd << 0)
        );

        $writer->write_int8(
            ($this->ra << 7)
            | ($this->z << 4)
            | ($this->rcode << 0)
        );
    }

    public static function read(BinaryReader $reader): Header
    {
        $id = $reader->read_int16();

        $byte = $reader->read_int8();
        $qr = ($byte & (0x1 << 7)) >> 7;
        $opcode = ($byte & (0xF << 3)) >> 3;
        $aa = ($byte & (0x1 << 2)) >> 2;
        $tc = ($byte & (0x1 << 1)) >> 1;
        $rd = ($byte & (0x1 << 0)) >> 0;

        $byte = $reader->read_int8();
        $ra = ($byte & (0x1 << 7)) >> 7;
        $z = ($byte & (0x7 << 4)) >> 4;
        $rcode = ($byte & (0xF << 0)) >> 0;

        return new Header(
            $id, $qr, $opcode, $aa, $tc, $rd, $ra, $z, $rcode
        );
    }
}
