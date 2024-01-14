<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Graphs\Renderer;

use phpDocumentor\Guides\RenderContext;

use function md5;

final class TestRenderer implements DiagramRenderer
{
    public function render(RenderContext $renderContext, string $diagram): string|null
    {
        return md5($diagram);
    }
}
