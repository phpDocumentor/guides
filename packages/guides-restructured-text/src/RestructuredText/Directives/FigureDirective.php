<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

/**
 * Renders an image, example :
 *
 * .. figure:: image.jpg
 *      :width: 100
 *      :alt: An image
 *
 *      Here is an awesome caption
 */
class FigureDirective extends SubDirective
{
    public function __construct(protected Rule $startingRule)
    {
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
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $image = new ImageNode($directive->getData());
        $scalarOptions = $this->optionsToArray($directive->getOptions());
        $image = $image->withOptions([
            'width' => $scalarOptions['width'] ?? null,
            'height' => $scalarOptions['height'] ?? null,
            'alt' => $scalarOptions['alt'] ?? null,
            'scale' => $scalarOptions['scale'] ?? null,
            'target' => $scalarOptions['target'] ?? null,
            'class' => $scalarOptions['class'] ?? null,
            'name' => $scalarOptions['name'] ?? null,
        ]);

        return new FigureNode($image, new CollectionNode($collectionNode->getChildren()));
    }
}
