<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function dirname;

/**
 * Renders an image, example :
 *
 * .. image:: image.jpg
 *      :width: 100
 *      :title: An image
 */
class Image extends BaseDirective
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getName(): string
    {
        return 'image';
    }

    /** {@inheritDoc} */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $variable,
        string $data,
        array $options,
    ): Node {
        return new ImageNode(
            $this->urlGenerator->absoluteUrl(
                dirname($documentParserContext->getContext()->getCurrentAbsolutePath()),
                $data,
            ),
        );
    }
}
