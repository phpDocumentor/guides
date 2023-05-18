<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Utility;

use function trim;

class LineUtility
{
    public function isWhiteline(string|null $line): bool
    {
        if ($line === null) {
            return true;
        }

        return trim($line) === '';
    }
}
