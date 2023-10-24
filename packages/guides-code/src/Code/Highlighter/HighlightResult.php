<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

final class HighlightResult
{
    public function __construct(
        public readonly string $language,
        public readonly string $code,
    ) {
    }
}
