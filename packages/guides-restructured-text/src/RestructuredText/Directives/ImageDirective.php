<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function dirname;

/**
 * Renders an image, example :
 *
 * .. image:: image.jpg
 *      :width: 100
 *      :title: An image
 */
final class ImageDirective extends BaseDirective
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
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
            $this->documentNameResolver->absoluteUrl(
                dirname($blockContext->getDocumentParserContext()->getContext()->getCurrentAbsolutePath()),
                $directive->getData(),
            ),
        );
    }
}
