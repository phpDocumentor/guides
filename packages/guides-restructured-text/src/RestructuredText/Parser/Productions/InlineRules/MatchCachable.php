<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

interface MatchCachable
{
    public function isCacheable(): bool;
}
