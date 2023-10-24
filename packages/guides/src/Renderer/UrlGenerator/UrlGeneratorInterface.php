<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\RenderContext;

interface UrlGeneratorInterface
{
    /**
     * Create a url with a file ending derived from the output format
     */
    public function createFileUrl(string $filename, string $outputFormat = 'html', string|null $anchor = null): string;

    public function generateInternalUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string;
}
