<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Utility;

use function filter_var;
use function preg_match;

use const FILTER_VALIDATE_INT;

class AnnotationUtility
{
    public function isFootnoteKey(string $key): bool
    {
        return $this->isAnonymousFootnote($key)
            || $this->getFootnoteName($key) !== null
            || $this->getFootnoteNumber($key) !== null;
    }

    public function isAnonymousFootnote(string $key): bool
    {
        return $key === '#';
    }

    public function getFootnoteName(string $key): string|null
    {
        preg_match('/^[#][a-zA-Z0-9]*$/msi', $key, $matches);

        return $matches[0] ?? null;
    }

    public function getFootnoteNumber(string $key): int|null
    {
        $intValue = filter_var($key, FILTER_VALIDATE_INT);
        if ($intValue === false || $intValue < 1) {
            return null;
        }

        return $intValue;
    }
}
