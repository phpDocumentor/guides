<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Standarad rst `contents` directive
 *
 * Displays a table of content of the current page
 */
class ContentsDirective extends BaseDirective
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function getName(): string
    {
        return 'contents';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $options = $directive->getOptions();
        $absoluteUrl = $this->documentNameResolver->absoluteUrl(
            $blockContext->getDocumentParserContext()->getContext()->getDirName(),
            $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
        );

        return (new ContentMenuNode([$absoluteUrl]))
            ->withOptions($this->optionsToArray($options))
            ->withCaption($directive->getDataNode());
    }
}
