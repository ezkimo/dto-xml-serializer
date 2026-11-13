<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

/**
 * Namespace Enumeration mit ungültigen, aber im Testdokument unbenutzten
 * abgeleiteten Prefixen. Nur INV und CAC werden tatsächlich verwendet.
 */
enum ProblematicNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CAC = 'urn:example:commonaggregate';
    case UDT_2 = 'urn:example:unqualifieddata:2';
    case EXT_XML = 'urn:example:extension:xml';
    case CBC_ID = 'urn:example:commonbasic';
}
