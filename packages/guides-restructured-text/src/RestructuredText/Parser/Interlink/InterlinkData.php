<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Interlink;

class InterlinkData
{
    public function __construct(
        public readonly string $reference,
        public readonly string $interlink,
    ) {
    }
}
