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
use phpDocumentor\Guides\Nodes\InlineToken\AbstractLinkToken;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RenderContext;
use Twig\Error\LoaderError;

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

    public function supports(Node $node): bool
    {
        return $node instanceof SpanNode;
    }

    public function linkToken(AbstractLinkToken $spanToken, RenderContext $context): string
    {
        if ($spanToken instanceof DocReferenceNode && $spanToken->getDocumentEntry() !== null) {
            $url = $context->relativeDocUrl($spanToken->getDocumentEntry()->getFile(), $spanToken->getAnchor());
            $spanToken->setUrl($url);
        } elseif ($spanToken instanceof ReferenceNode && $spanToken->getInternalTarget() !== null) {
            $url =  $context->relativeDocUrl(
                $spanToken->getInternalTarget()->getDocumentPath(),
                $spanToken->getInternalTarget()->getAnchor(),
            );
            $spanToken->setUrl($url);
        }

        return trim($this->renderer->renderTemplate(
            $context,
            'inline/' . $spanToken->getType() . '.html.twig',
            ['linkToken' => $spanToken],
        ));
    }

    public function genericTextRole(GenericTextRoleToken $token, RenderContext $renderContext): string
    {
        try {
            return trim($this->renderer->renderTemplate(
                $renderContext,
                'inline/textroles/' . $token->getType() . '.html.twig',
                ['textrole' => $token],
            ));
        } catch (LoaderError) {
            $this->logger->warning(
                'File "' . $renderContext->getCurrentFileName() . '" not template found for textrole "' . $token->getType() . '"',
                $renderContext->getLoggerInformation(),
            );

            return trim($this->renderer->renderTemplate(
                $renderContext,
                'inline/textroles/generic.html.twig',
                ['textrole' => $token],
            ));
        }
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
