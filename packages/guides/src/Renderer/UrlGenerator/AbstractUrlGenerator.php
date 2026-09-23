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
     * path: the memo in {@see self::generateInternalUrl()} is dropped on those two alone. Anything
     * else that decides the path has to be constant for the whole render - as the link style read
     * by {@see ConfigurableUrlGenerator} is, being set when the container is compiled. An
     * implementation that cannot hold to this overrides {@see self::generateInternalUrl()} and
     * calls the computation directly.
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

        $internalUrl = $this->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);

        // A subclass may render something of its own while it computes, and that nested call can arrive
        // here with another document, replacing the table below us. Only store what still belongs to it.
        if ($this->internalUrlCacheKey === $cacheKey) {
            $this->internalUrlCache[$canonicalUrl] = $internalUrl;
        }

        return $internalUrl;
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
