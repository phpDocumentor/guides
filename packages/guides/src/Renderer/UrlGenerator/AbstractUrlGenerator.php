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
use function preg_match;
use function sprintf;

use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_URL;

abstract class AbstractUrlGenerator implements UrlGeneratorInterface
{
    private const PLAIN_RELATIVE_PATH = '~^(?!/)[\x21-\x39\x3B-\x7E]+\z~';

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
        if (!$this->isRelativeUrl($canonicalUrl)) {
            throw new InvalidUrlException(sprintf('%s::%s may only be applied to relative URLs, %s cannot be handled', self::class, __METHOD__, $canonicalUrl));
        }

        return $this->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);
    }

    private function isRelativeUrl(string $url): bool
    {
        // Printable ASCII without a colon, not starting with a slash, has neither a scheme nor an
        // authority and so is a relative path, and League\Uri parses all of it without complaint;
        // AbstractUrlGeneratorIsRelativeUrlTest holds the two to the same answer. Canonical urls are
        // nearly always of this form, so most links skip the full parse. Anything else is still
        // decided by League\Uri, including the input it refuses with an exception.
        if (preg_match(self::PLAIN_RELATIVE_PATH, $url) === 1) {
            return true;
        }

        return BaseUri::from($url)->isRelativePath();
    }

    public function getCurrentFileUrl(RenderContext $renderContext): string
    {
        return $this->createFileUrl($renderContext, $renderContext->getCurrentFileName());
    }
}
