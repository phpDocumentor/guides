<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Table;

use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\TableSeparatorLineConfig;

interface TableBuilder
{
    public function pushSeparatorLine(TableSeparatorLineConfig $lineConfig): void;
    public function pushContentLine(string $line): void;
    public function buildNode(MarkupLanguageParser $parser, LineChecker $lineChecker);
}
