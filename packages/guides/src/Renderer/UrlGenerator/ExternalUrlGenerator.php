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
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use phpDocumentor\Guides\RenderContext;
use RuntimeException;

use function method_exists;
use function substr;

/**
 * Prefixes internal URLs (non-documents) with a pre-defined base URI.
 *
 * This is mostly useful when assets are uploaded outside of the documentation
 * project (e.g. a dedicated CDN). The other URL generation methods are
 * delegated to the wrapped URL generator.
 */
final class ExternalUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $baseUri,
    ) {
    }

    public function createFileUrl(RenderContext $context, string $filename, string|null $anchor = null): string
    {
        return $this->urlGenerator->createFileUrl($context, $filename, $anchor);
    }

    public function generateCanonicalOutputUrl(RenderContext $context, string $reference, string|null $anchor = null): string
    {
        return $this->urlGenerator->generateCanonicalOutputUrl($context, $reference, $anchor);
    }

    public function generateInternalUrl(RenderContext $renderContext, string $canonicalUrl): string
    {
        $uriFactory = static function (string $uri): Uri|BaseUri {
            // @phpstan-ignore function.alreadyNarrowedType
            $leagueUri = method_exists(Uri::class, 'parse') ? Uri::parse($uri) :  BaseUri::from($uri);
            if ($leagueUri === null) {
                throw new RuntimeException('Cannot create a URI from: "' . $uri . '"');
            }

            return $leagueUri;
        };

        $baseUri = $uriFactory($this->baseUri);
        $canonicalUrl = Uri::new($canonicalUrl);

        $strip = 0;
        if (!$baseUri->isAbsolute()) {
            // if only a path is given, prefix with a scheme that we drop at the end
            $baseUri = $uriFactory('phpdoc://' . $this->baseUri);
            $strip = 9;
        }

        $resolvedUri = $baseUri->resolve($canonicalUrl);
        $internalUrl = (string) (!$resolvedUri instanceof UriInterface ? $resolvedUri->getUri() : $resolvedUri);

        return substr($internalUrl, $strip);
    }
}
