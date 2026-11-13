<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

/**
 * Namespace Enumeration, deren abgeleitete Prefixe in Großbuchstaben vorliegen
 * und vom Serializer (Legacy-Verhalten) kleingeschrieben werden.
 */
enum LegacyMixedCaseNamespaces: string
{
    case INVOICE_UBL = 'urn:example:invoice';
    case COMMONAGGREGATE_RAM = 'urn:example:commonaggregate';
}
