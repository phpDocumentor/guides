<?php

namespace phpDocumentor\Guides\Renderer;

class DefaultTypeRendererFactory implements TypeRendererFactory
{
    /**
     * @var TypeRenderer[]
     */
    private array $renderSets = [];

    public function __construct()
    {
        $this->renderSets = [
            new HtmlTypeRenderer(),
            new IntersphinxTypeRenderer(),
        ];
    }

    public function getRenderSet(string $outputFormat) : TypeRenderer
    {
        foreach ($this->renderSets as $renderSet) {
            if ($renderSet->supports($outputFormat)) {
                return $renderSet;
            }
        }
        throw new \Exception(sprintf('No render set found for output format "%s"', $outputFormat));
    }
}
