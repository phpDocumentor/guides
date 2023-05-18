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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\NodeRenderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\References\ResolvedReference;
use phpDocumentor\Guides\RenderContext;

use function htmlspecialchars;
use function trim;

use const ENT_QUOTES;

class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    public function emphasis(string $text, RenderContext $renderContext): string
    {
        return trim($this->renderer->renderTemplate($renderContext, 'inline/emphasis.html.twig', ['text' => $text]));
    }

    public function strongEmphasis(string $text, RenderContext $renderContext): string
    {
        return trim($this->renderer->renderTemplate($renderContext, 'inline/strong-emphasis.html.twig', ['text' => $text]));
    }

    public function nbsp(RenderContext $renderContext): string
    {
        return '&nbsp;';

        // TODO: this is called in DocumentNode's getTitle function during parsing; wtf?
        // return $this->renderer->render('nbsp.html.twig');
    }

    public function br(RenderContext $renderContext): string
    {
        return '<br>';

        // TODO: this is called in DocumentNode's getTitle function during parsing; wtf?
        // return $this->renderer->render('br.html.twig');
    }

    public function literal(LiteralToken $token, RenderContext $renderContext): string
    {
        return trim($this->renderer->renderTemplate($renderContext, 'inline/literal.html.twig', ['node' => $token]));
    }

    /** @param string[] $attributes */
    public function link(RenderContext $context, string|null $url, string $title, array $attributes = []): string
    {
        $url = (string) $url;

        return trim($this->renderer->renderTemplate(
            $context,
            'inline/link.html.twig',
            [
                'url' => $this->urlGenerator->generateUrl($url),
                'text' => $title ?: $url,
                'attributes' => $attributes,
            ],
        ));
    }

    public function escape(string $span, RenderContext $renderContext): string
    {
        return htmlspecialchars($span, ENT_QUOTES);
    }

    /** @param array<string|null> $value */
    public function reference(RenderContext $renderContext, ResolvedReference $reference, array $value): string
    {
        $text = $value['text'] ?: $reference->getText();
        $text = trim($text);

        // reference to another document
        if ($reference->getUrl() !== null) {
            $url = $reference->getUrl();

            if ($value['anchor'] !== null) {
                $url .= '#' . $value['anchor'];
            }

            $link = $this->link($renderContext, $url, $text, $reference->getAttributes());

            // reference to anchor in existing document
        } elseif ($value['url'] !== null) {
            $url = $renderContext->getLink($value['url']);

            $link = $this->link($renderContext, $url, $text, $reference->getAttributes());
        } else {
            $link = $this->link($renderContext, '#', $text . ' (unresolved reference)', $reference->getAttributes());
        }

        return $link;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof SpanNode;
    }

    public function citation(CitationTarget $citationTarget, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate(
            $renderContext,
            'inline/citation.html.twig',
            [
                'url' => $renderContext->relativeDocUrl($citationTarget->getDocumentPath(), $citationTarget->getAnchor()),
                'citation' => $citationTarget,
            ],
        );
    }

    public function footnote(FootnoteTarget $footnoteTarget, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate(
            $renderContext,
            'inline/footnote.html.twig',
            [
                'url' => $renderContext->relativeDocUrl($footnoteTarget->getDocumentPath(), $footnoteTarget->getAnchor()),
                'footnote' => $footnoteTarget,
            ],
        );
    }
}
