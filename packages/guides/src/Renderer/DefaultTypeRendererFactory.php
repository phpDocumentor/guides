<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use Exception;

use function sprintf;

class DefaultTypeRendererFactory implements TypeRendererFactory
{
    /** @var TypeRenderer[] */
    private array $renderSets = [];

    public function __construct()
    {
        $this->renderSets = [
            new HtmlRenderer(),
            new LatexRenderer(),
            new IntersphinxRenderer(),
        ];
    }

    public function getRenderSet(string $outputFormat): TypeRenderer
    {
        foreach ($this->renderSets as $renderSet) {
            if ($renderSet->supports($outputFormat)) {
                return $renderSet;
            }
        }

        throw new Exception(sprintf('No render set found for output format "%s"', $outputFormat));
    }
}
