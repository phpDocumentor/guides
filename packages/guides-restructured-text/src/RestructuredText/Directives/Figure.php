<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\UrlGenerator;

use function assert;

/**
 * Renders an image, example :
 *
 * .. figure:: image.jpg
 *      :width: 100
 *      :alt: An image
 *
 *      Here is an awesome caption
 */
class Figure extends SubDirective
{
    private UrlGenerator $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getName(): string
    {
        return 'figure';
    }

    public function processSub(
        Node   $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $image = new ImageNode($this->urlGenerator->relativeUrl($data));
        $scalarOptions = $this->optionsToArray($options);
        $image = $image->withOptions([
            'width' => $scalarOptions['width'] ?? null,
            'height' => $scalarOptions['height'] ?? null,
            'alt' => $scalarOptions['alt'] ?? null,
            'scale' => $scalarOptions['scale'] ?? null,
            'target' => $scalarOptions['target'] ?? null,
            'class' => $scalarOptions['class'] ?? null,
            'name' => $scalarOptions['name'] ?? null,
        ]);

        return new FigureNode($image, $document);
    }
}
