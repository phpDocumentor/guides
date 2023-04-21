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

namespace phpDocumentor\Guides\NodeRenderers\LaTeX;

use phpDocumentor\Guides\NodeRenderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

use function is_string;
use function substr;

class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    public function emphasis(string $text, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate($renderContext, 'roles/emphasis.tex.twig', ['text' => $text]);
    }

    public function strongEmphasis(string $text, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate($renderContext, 'roles/strong-emphasis.tex.twig', ['text' => $text]);
    }

    public function nbsp(RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate($renderContext, 'roles/nbsp.tex.twig');
    }

    public function br(RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate($renderContext, 'roles/br.tex.twig');
    }

    public function literal(LiteralToken $token, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate($renderContext, 'roles/literal.tex.twig', ['node' => $token]);
    }

    /** @param string[] $attributes */
    public function link(RenderContext $context, string|null $url, TitleNode|string $title, array $attributes = []): string
    {
        $type = 'href';

        if (is_string($url) && $url !== '' && $url[0] === '#') {
            $type = 'ref';

            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';
            $url = $context->getCurrentFileName() . $url;
        }

        return $this->renderer->renderTemplate(
            $context,
            'roles/link.tex.twig',
            [
                'type' => $type,
                'url' => $url,
                'title' => $title,
                'attributes' => $attributes,
            ],
        );
    }

    public function escape(string $span, RenderContext $renderContext): string
    {
        return $span;
    }

    /** @param string[] $value */
    public function reference(RenderContext $renderContext, ResolvedReference $reference, array $value): string
    {
        $text = $value['text'] ?: $reference->getText();
        $url = $reference->getUrl();

        if ($value['anchor'] !== '') {
            $url .= $value['anchor'];
        }

        if ($url === null) {
            $url = '';
        }

        return $this->link($renderContext, $url, $text);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof SpanNode;
    }
}
