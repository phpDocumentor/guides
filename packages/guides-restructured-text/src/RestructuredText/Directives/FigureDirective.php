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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

use function dirname;

/**
 * Renders an image, example :
 *
 * .. figure:: image.jpg
 *      :width: 100
 *      :alt: An image
 *
 *      Here is an awesome caption
 */
#[Option(name: 'width', description: 'Width of the image in pixels')]
#[Option(name: 'height', description: 'Height of the image in pixels')]
#[Option(name: 'alt', description: 'Alternative text for the image')]
#[Option(name: 'scale', description: 'Scale of the image, e.g. 0.5 for half size')]
#[Option(name: 'target', description: 'Target for the image, e.g. a link to the image')]
#[Option(name: 'class', description: 'CSS class to apply to the image')]
#[Option(name: 'name', description: 'Name of the image, used for references')]
#[Option(name: 'align', description: 'Alignment of the image, e.g. left, right, center')]
final class FigureDirective extends SubDirective
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
        protected Rule $startingRule,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'figure';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $image = new ImageNode($this->documentNameResolver->absoluteUrl(
            dirname($blockContext->getDocumentParserContext()->getContext()->getCurrentAbsolutePath()),
            $directive->getData(),
        ));
        $scalarOptions = $this->optionsToArray($directive->getOptions());
        $image = $image->withOptions([
            'width' => $scalarOptions['width'] ?? null,
            'height' => $scalarOptions['height'] ?? null,
            'alt' => $scalarOptions['alt'] ?? null,
            'scale' => $scalarOptions['scale'] ?? null,
            'target' => $scalarOptions['target'] ?? null,
            'class' => $scalarOptions['class'] ?? null,
            'name' => $scalarOptions['name'] ?? null,
            'align' => $scalarOptions['align'] ?? null,
        ]);

        return new FigureNode($image, new CollectionNode($collectionNode->getChildren()));
    }
}
