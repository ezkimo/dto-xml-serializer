<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

enum Namespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC = 'urn:example:commonaggregate';
    case UDT = 'urn:example:unqualifieddata';
    case EXT = 'urn:example:extension';
}