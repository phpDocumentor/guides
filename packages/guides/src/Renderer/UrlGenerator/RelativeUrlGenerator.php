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
    /**
     * Relative paths computed for the document currently being rendered, keyed by canonical url.
     *
     * A relative path only depends on the two paths, so it may be reused — but this is a service that
     * lives for the whole render, and every document renders a menu over the same targets. Keeping the
     * output file in the key would let the table grow as documents times targets. It is therefore held
     * for one document and dropped when the next one starts.
     *
     * @var array<string, string>
     */
    private array $pathCache = [];

    private string|null $pathCacheOutputFilePath = null;

    public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        $outputFilePath = $renderContext->getOutputFilePath();

        if ($this->pathCacheOutputFilePath !== $outputFilePath) {
            $this->pathCache = [];
            $this->pathCacheOutputFilePath = $outputFilePath;
        }

        $cacheKey = $canonicalUrl;

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
