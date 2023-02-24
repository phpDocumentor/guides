<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class Meta extends Directive
{
    public function getName(): string
    {
        return 'meta';
    }

    public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {
        $document = $documentParserContext->getDocument();

        foreach ($options as $option) {
            $document->addHeaderNode(new MetaNode($option->getName(), (string) $option->getValue()));
        }

        return null;
    }
}
