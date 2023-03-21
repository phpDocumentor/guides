<?php

namespace phpDocumentor\Guides\Renderer;

interface TypeRendererFactory
{
    public function getRenderSet(string $outputFormat) : TypeRenderer;
}
