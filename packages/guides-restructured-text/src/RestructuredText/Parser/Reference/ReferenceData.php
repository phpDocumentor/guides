<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Reference;

final class ReferenceData
{
    public function __construct(
        public readonly string $reference,
        public readonly string|null $text,
    ) {
    }
}
