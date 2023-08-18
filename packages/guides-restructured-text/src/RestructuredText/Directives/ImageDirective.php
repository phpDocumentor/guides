<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function dirname;

/**
 * Renders an image, example :
 *
 * .. image:: image.jpg
 *      :width: 100
 *      :title: An image
 */
class ImageDirective extends BaseDirective
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getName(): string
    {
        return 'image';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        return new ImageNode(
            $this->urlGenerator->absoluteUrl(
                dirname($blockContext->getDocumentParserContext()->getContext()->getCurrentAbsolutePath()),
                $directive->getData(),
            ),
        );
    }
}
