<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

enum AttributeFormType: string
{
    case QUALIFIED = 'qualified';
    case UNQUALIFIED = 'unqualified';
}