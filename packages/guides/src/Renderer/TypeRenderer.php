<?php

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;

interface TypeRenderer
{
    public function supports(string $outputFormat): bool;
    public function render(RenderCommand $renderCommand): void;
}
