<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\RenderContext;

interface UrlGeneratorInterface
{
    /**
     * Create a url with a file ending derived from the output format
     */
    public function createFileUrl(RenderContext $context, string $filename, string|null $anchor = null): string;

    public function generateCanonicalOutputUrl(RenderContext $context, string $reference, string|null $anchor = null): string;

    public function generateInternalUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string;
}
