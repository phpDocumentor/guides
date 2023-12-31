<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Interlink;

interface InterlinkParser
{
    public function extractInterlink(string $fullReference): InterlinkData;
}
