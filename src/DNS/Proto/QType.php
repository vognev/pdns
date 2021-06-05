<?php

namespace PDNS\DNS\Proto;

use PDNS\Support\Enum;

/**
 * @method static QType A()
 * @method static QType NS()
 * @method static QType CNAME()
 * @method static QType SOA()
 * @method static QType PTR()
 * @method static QType MX()
 * @method static QType TXT()
 * @method static QType OPTION()
 */
class QType
{
    use Enum;

    const A = 1;
    const NS = 2;
    const CNAME = 5;
    const SOA = 6;
    const PTR = 12;
    const MX = 15;
    const TXT = 16;
    const OPTION = 41;
}
