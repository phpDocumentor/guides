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

use League\Uri\BaseUri;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\Exception\InvalidUrlException;

use function filter_var;
use function sprintf;

use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_URL;

abstract class AbstractUrlGenerator implements UrlGeneratorInterface
{
    /** @var array<string, string> */
    private array $internalUrlCache = [];

    private string|null $internalUrlCacheKey = null;

    public function __construct(private readonly DocumentNameResolverInterface $documentNameResolver)
    {
    }

    public function createFileUrl(RenderContext $context, string $filename, string|null $anchor = null): string
    {
        $anchorSuffix = $anchor !== null && $anchor !== '' ? '#' . $anchor : '';

        if ($filename === '') {
            return $anchorSuffix !== '' ? $anchorSuffix : '#';
        }

        return $filename . '.' . $context->getOutputFormat() . $anchorSuffix;
    }

    /**
     * Turn a canonical URL into the path to write into the document being rendered.
     *
     * The result is reused for every further link with the same canonical URL in that document, so
     * it may depend on the render context only through the output file path and the destination
     * path. An implementation that reads anything else from the context has to say so, because the
     * memo in {@see self::generateInternalUrl()} is dropped on those two alone.
     */
    abstract public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string;

    /**
     * Generate a canonical output URL with the configured file extension and anchor
     */
    public function generateCanonicalOutputUrl(RenderContext $context, string $reference, string|null $anchor = null): string
    {
        if (filter_var($reference, FILTER_VALIDATE_URL) !== false) {
            return $reference;
        }

        // Pass through email addresses (for mailto: link generation)
        if (filter_var($reference, FILTER_VALIDATE_EMAIL) !== false) {
            return $reference;
        }

        // If reference is already a known document, it's already canonical - use directly
        if ($context->getProjectNode()->findDocumentEntry($reference) !== null) {
            return $this->generateInternalUrl(
                $context,
                $this->createFileUrl($context, $reference, $anchor),
            );
        }

        // Otherwise, resolve relative reference to canonical path
        $canonicalUrl = $this->documentNameResolver->canonicalUrl(
            $context->getDirName(),
            $reference,
        );

        return $this->generateInternalUrl(
            $context,
            $this->createFileUrl($context, $canonicalUrl, $anchor),
        );
    }

    public function generateInternalUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        $cacheKey = $renderContext->getOutputFilePath() . "\0" . $renderContext->getDestinationPath();

        if ($this->internalUrlCacheKey !== $cacheKey) {
            $this->internalUrlCache = [];
            $this->internalUrlCacheKey = $cacheKey;
        }

        if (isset($this->internalUrlCache[$canonicalUrl])) {
            return $this->internalUrlCache[$canonicalUrl];
        }

        if (!$this->isRelativeUrl($canonicalUrl)) {
            throw new InvalidUrlException(sprintf('%s::%s may only be applied to relative URLs, %s cannot be handled', self::class, __METHOD__, $canonicalUrl));
        }

        return $this->internalUrlCache[$canonicalUrl] = $this->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);
    }

    private function isRelativeUrl(string $url): bool
    {
        return BaseUri::from($url)->isRelativePath();
    }

    public function getCurrentFileUrl(RenderContext $renderContext): string
    {
        return $this->createFileUrl($renderContext, $renderContext->getCurrentFileName());
    }
}
