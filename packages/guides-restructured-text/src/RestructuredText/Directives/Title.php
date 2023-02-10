<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\DocumentTitleNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
class Title extends Directive
{
    public function getName(): string
    {
        return 'title';
    }

    /**
     * @param DocumentParserContext $documentParserContext
     * @param string[] $options
     */
    public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {
        $document = $documentParserContext->getDocument();
        $document->addHeaderNode(new DocumentTitleNode($data));

        return null;
    }
}
