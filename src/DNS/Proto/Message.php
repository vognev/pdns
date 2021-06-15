<?php

namespace PDNS\DNS\Proto;

use PDNS\Binary\BinaryReader;
use PDNS\Binary\BinaryWriter;

class Message
{
    /**
     * @param Header $header
     * @param Question[] $questions
     * @param Record[] $answers
     * @param Record[] $authority
     * @param Record[] $additional
     */
    public function __construct(
        protected Header $header,
        protected array $questions,
        protected array $answers,
        protected array $authority,
        protected array $additional,
    ) {}

    public function answer(Record $record) : Message
    {
        $this->answers[] = $record;

        return $this;
    }

    public function reply() : Message
    {
        return new Message($this->header->reply(), $this->questions, [], [], []);
    }

    public static function read(BinaryReader $reader) : Message
    {
        $header = Header::read($reader);

        $questions = [];
        $qdcount = $reader->read_int16();

        $answers = [];
        $ancount = $reader->read_int16();

        $authority = [];
        $nscount = $reader->read_int16();

        $additional = [];
        $arcount = $reader->read_int16();

        while ($qdcount > 0) {
            $questions[] = Question::read($reader);
            $qdcount--;
        }

        while ($ancount > 0) {
            $answers[] = Record::read($reader);
            $ancount--;
        }

        while ($nscount > 0) {
            $authority[] = Record::read($reader);
            $nscount--;
        }

        while ($arcount > 0) {
            $additional[] = Record::read($reader);
            $arcount--;
        }

        return new self($header, $questions, $answers, $authority, $additional);
    }

    public function write(BinaryWriter $writer) : void
    {
        $this->header->write($writer);

        $writer->write_int16(count($this->questions));
        $writer->write_int16(count($this->answers));
        $writer->write_int16(count($this->authority));
        $writer->write_int16(count($this->additional));

        foreach ($this->questions as $question) {
            $question->write($writer);
        }

        foreach ($this->answers as $answer) {
            $answer->write($writer);
        }

        foreach ($this->authority as $authority) {
            $authority->write($writer);
        }

        foreach ($this->additional as $additional) {
            $additional->write($writer);
        }
    }
}
