<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Fixtures;

/**
 * Namespace Enumeration, bei der zwei unterschiedliche URIs denselben Prefix
 * ableiten (d16a).
 */
enum DuplicatePrefixNamespaces: string
{
    case INV = 'urn:example:invoice';
    case CBC_D16A = 'urn:example:commonbasic';
    case CAC_D16A = 'urn:example:commonaggregate';
}
