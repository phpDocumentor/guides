<?php

    declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Setup\QuickStart;
use phpDocumentor\Guides\UrlGenerator;

class DefaultTypeRendererFactory implements TypeRendererFactory
{
    private InMemoryRendererFactory $factory;

    public function __construct()
    {
        $this->factory = new InMemoryRendererFactory([
            new HtmlRenderer(QuickStart::createRenderer()),
            new LatexRenderer(),
            new IntersphinxRenderer(new UrlGenerator()),
        ]);
    }

    public function getRenderSet(string $outputFormat): TypeRenderer
    {
        return $this->factory->getRenderSet($outputFormat);
    }
}
