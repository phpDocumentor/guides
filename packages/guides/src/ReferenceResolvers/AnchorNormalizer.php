<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

/**
 * Normalizes raw data used to generate anchors.
 *
 * Can be used to ignore case and special signs.
 */
interface AnchorNormalizer
{
    public function reduceAnchor(string $rawAnchor): string;
}
