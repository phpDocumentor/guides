<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
class Replace extends Directive
{
    private SpanParser $spanParser;

    public function __construct(SpanParser $spanParser)
    {
        $this->spanParser = $spanParser;
    }

    public function getName(): string
    {
        return 'replace';
    }

    /** {@inheritDoc} */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $variable,
        string $data,
        array $options
    ): Node {
        return $this->spanParser->parse($data, $documentParserContext->getParser()->getParserContext());
    }
}
