<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

enum UnindentStrategy
{
    case ALL;
    case FIRST; // only take the first line into account
    case NONE;
}
