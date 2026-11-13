<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

/**
 * Namespace Enumeration, deren für CAC abgeleiteter Prefix mit einer Ziffer
 * beginnt und damit ungültig ist, sobald CAC im Dokument verwendet wird.
 */
enum InvalidUsedNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC = 'urn:example:commonbasic';
    case CAC_2010A = 'urn:example:commonaggregate';
}
