<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\UrlGeneratorInterface;

/**
 * Standarad rst `contents` directive
 *
 * Displays a table of content of the current page
 */
class ContentsDirective extends BaseDirective
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getName(): string
    {
        return 'contents';
    }

    /** {@inheritDoc} */
    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        $options = $directive->getOptions();
        $absoluteUrl = $this->urlGenerator->absoluteUrl(
            $documentParserContext->getContext()->getDirName(),
            $documentParserContext->getContext()->getCurrentFileName(),
        );

        return (new ContentMenuNode([$absoluteUrl]))
            ->withOptions($this->optionsToArray($options));
    }
}
