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
use function rtrim;
use function str_replace;

final class DocumentNameResolver implements DocumentNameResolverInterface
{
    /**
     * Canonical urls computed so far, keyed by base path and url.
     *
     * This table lives for the whole render and is never dropped: its entries are asked for again from
     * document after document, so it grows with the distinct pairs of a project, not with its calls -
     * 8,704 pairs for 294,026 calls on the 1003 documents of TYPO3CMS-Reference-CoreApi.
     *
     * @var array<string, string>
     */
    private array $canonicalUrlCache = [];

    /**
     * Returns the absolute path, including prefixing '/'.
     *
     * This method will, by design, return an absolute path including the prefixing slash. The slash will make it clear
     * to the other URL generating methods that this need not be resolved and can stay the same.
     */
    public function absoluteUrl(string $basePath, string $url): string
    {
        $uri = BaseUri::from($url);
        if ($uri->isAbsolute()) {
            return $url;
        }

        if ($uri->isAbsolutePath()) {
            return $url;
        }

        return '/' . str_replace('./', '', $this->canonicalUrl($basePath, $url));
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
        return $this->canonicalUrlCache[$basePath . "\0" . $url] ??= $this->computeCanonicalUrl($basePath, $url);
    }

    private function computeCanonicalUrl(string $basePath, string $url): string
    {
        if ($url[0] === '/') {
            return ltrim($url, '/');
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

        return ltrim(rtrim(implode('/', $dirNameParts), '/') . '/' . implode('/', $urlPass1), '/');
    }
}
