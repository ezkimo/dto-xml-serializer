<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

enum AttributeUseType: string
{
    case OPTIONAL = 'optional';
    case PROHIBITED = 'prohibited';
    case REQUIRED = 'required';
}