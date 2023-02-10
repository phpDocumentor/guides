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

        foreach ($options as $key => $value) {
            $document->addHeaderNode(new MetaNode($key, $value));
        }

        return null;
    }
}
