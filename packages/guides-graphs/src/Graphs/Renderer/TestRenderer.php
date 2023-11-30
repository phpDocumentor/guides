<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Graphs\Renderer;

use function md5;

final class TestRenderer implements DiagramRenderer
{
    public function render(string $diagram): string|null
    {
        return md5($diagram);
    }
}
