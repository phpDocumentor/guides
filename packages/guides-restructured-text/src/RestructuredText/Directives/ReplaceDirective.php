<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
class ReplaceDirective extends BaseDirective
{
    public function __construct(private readonly SpanParser $spanParser)
    {
    }

    public function getName(): string
    {
        return 'replace';
    }

    /** {@inheritDoc} */
    public function processNode(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node {
        return $this->spanParser->parse($directive->getData(), $documentParserContext->getParser()->getParserContext());
    }
}
