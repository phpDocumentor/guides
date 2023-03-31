<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

interface TypeRendererFactory
{
    public function getRenderSet(string $outputFormat): TypeRenderer;
}
