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

use function rtrim;

final class AbsoluteUrlGenerator extends AbstractUrlGenerator
{
    public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        if ($renderContext->getDestinationPath() === '') {
            return $canonicalUrl;
        }

        return rtrim($renderContext->getDestinationPath(), '/') . '/' . $canonicalUrl;
    }
}
