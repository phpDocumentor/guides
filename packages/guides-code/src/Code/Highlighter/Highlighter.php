<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

interface Highlighter
{
    /** @param array<string, string|null> $debugInformation */
    public function __invoke(string $language, string $code, array $debugInformation): HighlightResult;
}
