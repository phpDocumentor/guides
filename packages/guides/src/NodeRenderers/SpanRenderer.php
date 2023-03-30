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

use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Span\LiteralToken;

interface SpanRenderer
{
    public function emphasis(string $text, RenderContext $renderContext): string;

    public function strongEmphasis(string $text, RenderContext $renderContext): string;

    public function nbsp(RenderContext $renderContext): string;

    public function br(RenderContext $renderContext): string;

    public function literal(LiteralToken $token, RenderContext $renderContext): string;

    /**
     * @param string[] $attributes
     */
    public function link(RenderContext $context, ?string $url, string $title, array $attributes = []): string;

    public function escape(string $span, RenderContext $renderContext): string;

    /**
     * @param string[] $value
     */
    public function reference(RenderContext $environment, ResolvedReference $reference, array $value): string;
}
