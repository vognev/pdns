<?php

namespace PDNS\DNS\Proto;

enum QType : int
{
    case A      = 1;
    case NS     = 2;
    case CNAME  = 5;
    case SOA    = 6;
    case PTR    = 12;
    case MX     = 15;
    case TXT    = 16;
    case OPTION = 41;
}
