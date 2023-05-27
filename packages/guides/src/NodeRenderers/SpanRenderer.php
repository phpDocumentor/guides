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

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\Nodes\InlineToken\AbstractLinkToken;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\RenderContext;

interface SpanRenderer
{
    public function emphasis(string $text, RenderContext $renderContext): string;

    public function strongEmphasis(string $text, RenderContext $renderContext): string;

    public function nbsp(RenderContext $renderContext): string;

    public function br(RenderContext $renderContext): string;

    public function literal(LiteralToken $token, RenderContext $renderContext): string;

    public function genericTextRole(GenericTextRoleToken $token, RenderContext $renderContext): string;

    public function citation(CitationTarget $citationTarget, RenderContext $renderContext): string;

    public function footnote(FootnoteTarget $footnoteTarget, RenderContext $renderContext): string;

    /** @param string[] $attributes */
    public function link(RenderContext $context, string|null $url, string $title, array $attributes = []): string;

    public function linkToken(AbstractLinkToken $spanToken, RenderContext $context): string;

    public function escape(string $span, RenderContext $renderContext): string;
}
