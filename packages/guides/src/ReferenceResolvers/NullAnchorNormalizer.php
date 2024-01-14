<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

class NullAnchorNormalizer implements AnchorNormalizer
{
    public function reduceAnchor(string $rawAnchor): string
    {
        return $rawAnchor;
    }
}
