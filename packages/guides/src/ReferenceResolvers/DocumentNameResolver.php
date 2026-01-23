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

namespace phpDocumentor\Guides\ReferenceResolvers;

use League\Uri\BaseUri;

use function array_pop;
use function explode;
use function implode;
use function ltrim;
use function trim;

final class DocumentNameResolver implements DocumentNameResolverInterface
{
    /** @var array<string, string> */
    private array $absoluteUrlCache = [];

    /** @var array<string, string> */
    private array $canonicalUrlCache = [];

    /** @var array<string, bool> */
    private array $isAbsoluteCache = [];

    /** @var array<string, bool> */
    private array $isAbsolutePathCache = [];

    /**
     * Returns the absolute path, including prefixing '/'.
     *
     * This method will, by design, return an absolute path including the prefixing slash. The slash will make it clear
     * to the other URL generating methods that this need not be resolved and can stay the same.
     */
    public function absoluteUrl(string $basePath, string $url): string
    {
        $cacheKey = $basePath . '|' . $url;
        if (isset($this->absoluteUrlCache[$cacheKey])) {
            return $this->absoluteUrlCache[$cacheKey];
        }

        // Cache URI analysis results separately by URL
        if (!isset($this->isAbsoluteCache[$url])) {
            $uri = BaseUri::from($url);
            $this->isAbsoluteCache[$url] = $uri->isAbsolute();
            $this->isAbsolutePathCache[$url] = $uri->isAbsolutePath();
        }

        if ($this->isAbsoluteCache[$url]) {
            return $this->absoluteUrlCache[$cacheKey] = $url;
        }

        if ($this->isAbsolutePathCache[$url]) {
            return $this->absoluteUrlCache[$cacheKey] = $url;
        }

        if ($basePath === '/') {
            return $this->absoluteUrlCache[$cacheKey] = $basePath . $url;
        }

        return $this->absoluteUrlCache[$cacheKey] = '/' . trim($basePath, '/') . '/' . $url;
    }

    /**
     * Returns the Path used in the Metas to find this file.
     *
     * The Metas collection, which is used to build the table of contents, uses these canonical paths as a unique
     * identifier to find the metadata for that file. Technically speaking, the canonical URL is the absolute URL
     * without the preceeding slash. But due to the many locations that this method is used; it will do its own
     * resolving.
     */
    public function canonicalUrl(string $basePath, string $url): string
    {
        $cacheKey = $basePath . '|' . $url;
        if (isset($this->canonicalUrlCache[$cacheKey])) {
            return $this->canonicalUrlCache[$cacheKey];
        }

        if ($url[0] === '/') {
            return $this->canonicalUrlCache[$cacheKey] = ltrim($url, '/');
        }

        $dirNameParts = explode('/', $basePath);
        $urlParts = explode('/', $url);
        $urlPass1 = [];

        foreach ($urlParts as $part) {
            if ($part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($dirNameParts);
                continue;
            }

            $urlPass1[] = $part;
        }

        return $this->canonicalUrlCache[$cacheKey] = ltrim(implode('/', $dirNameParts) . '/' . implode('/', $urlPass1), '/');
    }
}
