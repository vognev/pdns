<?php

namespace PDNS\DNS\Proto;

use PDNS\Support\BinaryReader;
use PDNS\Support\BinaryWriter;

class QName
{
    private array $labels;

    public function __construct(string ...$labels)
    {
        $this->labels = $labels;
    }

    public static function read(BinaryReader $reader) : QName
    {
        $labels = [];
        $retpos = 0;

        while (true) {
            $len = $reader->read_int8();
            if (0 == $len) {
                break;
            }

            if (0b11 == ($len >> 6)) {
                $offset = (($len - 192) << 8) | $reader->read_int8();
                $oldpos = $reader->seek($offset);
                if (0 == $retpos) {
                    $retpos = $oldpos;
                }
            } else {
                $labels[] = $reader->read_data($len);
            }
        }

        if ($retpos > 0) {
            $reader->seek($retpos);
        }

        return new QName(...$labels);
    }

    public function write(BinaryWriter $writer)
    {
        foreach ($this->labels as $label) {
            $writer->write_int8(strlen($label));
            $writer->write_data($label);
        }

        $writer->write_int8(0);
    }

    public function __toString(): string
    {
        return implode('.', $this->labels);
    }
}
