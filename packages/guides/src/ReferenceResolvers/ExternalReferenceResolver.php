<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

use function parse_url;
use function preg_match;

use const PHP_URL_SCHEME;

/**
 * Resolves references with an embedded external URL.
 *
 * A link is external if it starts with a scheme defined in the IANA Registry
 * of URI Schemes.
 *
 * @see https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
 */
class ExternalReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 1000;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        $url = parse_url($node->getTargetReference(), PHP_URL_SCHEME);
        if ($url !== null && $url !== false && preg_match('/^' . InlineLexer::SUPPORTED_TLDS . '$/', $url)) {
            $node->setUrl($node->getTargetReference());

            return true;
        }

        return false;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
