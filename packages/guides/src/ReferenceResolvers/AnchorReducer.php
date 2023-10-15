<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

/**
 * Reduces the complexity of raw data used to generate anchors.
 *
 * Can be used to ignore case and special signs.
 */
interface AnchorReducer
{
    public function reduceAnchor(string $rawAnchor): string;
}
