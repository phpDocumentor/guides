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

use Exception;
use League\Uri\UriInfo;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UriFactory;

use function sprintf;

abstract class AbstractUrlGenerator implements UrlGeneratorInterface
{
    public function createFileUrl(string $filename, string $outputFormat = 'html', string|null $anchor = null): string
    {
        return $filename . '.' . $outputFormat .
            ($anchor !== null ? '#' . $anchor : '');
    }

    abstract public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string;

    public function generateInternalUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        if (!$this->isRelativeUrl($canonicalUrl)) {
            throw new Exception(sprintf('%s::%s may only be applied to relative URLs, %s cannot be handled', self::class, __METHOD__, $canonicalUrl));
        }

        return $this->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);
    }

    private function isRelativeUrl(string $url): bool
    {
        $uri = UriFactory::createUri($url);

        return UriInfo::isRelativePath($uri);
    }
}
