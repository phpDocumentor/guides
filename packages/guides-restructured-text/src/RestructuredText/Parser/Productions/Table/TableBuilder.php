<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;

interface TableBuilder
{
    public function buildNode(ParserContext $tableParserContext, DocumentParserContext $documentParserContext, LineChecker $lineChecker);
}
