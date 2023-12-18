<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

interface Highlighter
{
    public function __invoke(string $language, string $code): HighlightResult;
}
