<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

class NullAnchorReducer implements AnchorReducer
{
    public function reduceAnchor(string $rawAnchor): string
    {
        return $rawAnchor;
    }
}
