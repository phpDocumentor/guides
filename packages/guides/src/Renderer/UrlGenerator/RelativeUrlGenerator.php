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

use League\Uri\Uri;
use phpDocumentor\Guides\RenderContext;

use function array_pop;
use function array_slice;
use function count;
use function explode;
use function implode;
use function min;
use function str_repeat;

final class RelativeUrlGenerator extends AbstractUrlGenerator
{
    /** @var array<string, string> */
    private array $pathCache = [];

    public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        $outputFilePath = $renderContext->getOutputFilePath();
        $cacheKey = $outputFilePath . '|' . $canonicalUrl;

        if (isset($this->pathCache[$cacheKey])) {
            return $this->pathCache[$cacheKey];
        }

        $currentPathUri = Uri::new($outputFilePath);
        $canonicalUrlUri = Uri::new($canonicalUrl);

        $canonicalAnchor = $canonicalUrlUri->getFragment();

        // If the paths are the same, include the anchor
        if ($currentPathUri->getPath() === $canonicalUrlUri->getPath()) {
            return $this->pathCache[$cacheKey] = '#' . $canonicalAnchor;
        }

        // Split paths into arrays
        $currentPathParts = explode('/', $currentPathUri->getPath());
        $canonicalPathParts = explode('/', $canonicalUrlUri->getPath());

        // Remove filename from current path
        array_pop($currentPathParts);

        // Find common path length
        $commonLength = 0;
        $minLength = min(count($canonicalPathParts), count($currentPathParts));

        while ($commonLength < $minLength && $canonicalPathParts[$commonLength] === $currentPathParts[$commonLength]) {
            $commonLength++;
        }

        // Calculate relative path
        $relativePath = str_repeat('../', count($currentPathParts) - $commonLength);

        // Append the remaining path from the canonical URL
        $relativePath .= implode('/', array_slice($canonicalPathParts, $commonLength));

        // Add anchor if present in the canonical URL
        if (!empty($canonicalAnchor)) {
            $relativePath .= '#' . $canonicalAnchor;
        }

        return $this->pathCache[$cacheKey] = $relativePath;
    }
}
