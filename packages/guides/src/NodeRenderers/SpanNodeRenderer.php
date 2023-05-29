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

use InvalidArgumentException;
use phpDocumentor\Guides\Nodes\InlineToken\AbstractLinkToken;
use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function assert;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_replace;
use function trim;

/** @implements NodeRenderer<SpanNode> */
abstract class SpanNodeRenderer implements NodeRenderer, SpanRenderer, NodeRendererFactoryAware
{
    private NodeRendererFactory|null $nodeRendererFactory = null;

    public function __construct(
        protected TemplateRenderer $renderer,
        protected readonly LoggerInterface $logger,
        protected UrlGeneratorInterface $urlGenerator,
    ) {
    }

    abstract public function nbsp(RenderContext $renderContext): string;

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof SpanNode === false) {
            throw new InvalidArgumentException('Invalid node presented');
        }

        $value = $node->getValue();

        $span = $this->renderSyntaxes($value, $renderContext);

        return $this->renderTokens($node, $span, $renderContext);
    }

    /** @param string[] $attributes */
    public function link(RenderContext $context, string|null $url, string $title, array $attributes = []): string
    {
        $url = (string) $url;

        return $this->renderer->renderTemplate(
            $context,
            'link.html.twig',
            [
                'url' => $this->urlGenerator->generateUrl($url),
                'title' => $title,
                'attributes' => $attributes,
            ],
        );
    }

    private function renderSyntaxes(string $span, RenderContext $renderContext): string
    {
        $span = $this->escape($span, $renderContext);

        $span = $this->renderStrongEmphasis($span, $renderContext);

        $span = $this->renderEmphasis($span, $renderContext);

        $span = $this->renderNbsp($span, $renderContext);

        $span = $this->renderVariables($span, $renderContext);

        return $this->renderBrs($span, $renderContext);
    }

    private function renderStrongEmphasis(string $span, RenderContext $renderContext): string
    {
        return preg_replace_callback(
            '/\*\*(.+)\*\*/mUsi',
            fn (array $matches): string => trim($this->strongEmphasis($matches[1], $renderContext)),
            $span,
        ) ?? '';
    }

    private function renderEmphasis(string $span, RenderContext $renderContext): string
    {
        return preg_replace_callback(
            '/\*(.+)\*/mUsi',
            fn (array $matches): string => trim($this->emphasis($matches[1], $renderContext)),
            $span,
        ) ?? '';
    }

    private function renderNbsp(string $span, RenderContext $renderContext): string
    {
        return preg_replace('/~/', $this->nbsp($renderContext), $span) ?? '';
    }

    private function renderVariables(string $span, RenderContext $context): string
    {
        return preg_replace_callback(
            '/\|(.+)\|/mUsi',
            function (array $match) use ($context): string {
                $variable = $context->getVariable($match[1], '');

                if ($variable instanceof Node) {
                    assert($this->nodeRendererFactory !== null);

                    return $this->nodeRendererFactory->get($variable)->render($variable, $context);
                }

                return $variable;
            },
            $span,
        ) ?? '';
    }

    private function renderBrs(string $span, RenderContext $renderContext): string
    {
        // Adding brs when a space is at the end of a line
        return preg_replace('/ \n/', $this->br($renderContext), $span) ?? '';
    }

    private function renderTokens(SpanNode $node, string $span, RenderContext $context): string
    {
        foreach ($node->getTokens() as $token) {
            $span = $this->renderToken($token, $span, $context);
        }

        return $span;
    }

    private function renderToken(InlineMarkupToken $spanToken, string $span, RenderContext $context): string
    {
        switch (true) {
            case $spanToken instanceof LiteralToken:
                return trim($this->renderLiteral($spanToken, $span, $context));

            case $spanToken instanceof AbstractLinkToken:
                return trim($this->renderLinkToken($spanToken, $span, $context));

            case $spanToken instanceof GenericTextRoleToken:
                return trim($this->renderGenericTextRoleToken($spanToken, $span, $context));

            case $spanToken instanceof CitationInlineNode:
                assert($spanToken instanceof CitationInlineNode);

                return trim($this->renderCitation($spanToken, $span, $context));

            case $spanToken instanceof FootnoteInlineNode:
                assert($spanToken instanceof FootnoteInlineNode);

                return trim($this->renderFootnote($spanToken, $span, $context));

            case $spanToken instanceof HyperLinkNode:
                return trim($this->renderLink($spanToken, $span, $context));

            default:
                return $spanToken->getType();
        }
    }

    private function renderGenericTextRoleToken(GenericTextRoleToken $token, string $span, RenderContext $context): string
    {
        return str_replace(
            $token->getId(),
            $this->genericTextRole($token, $context),
            $span,
        );
    }

    private function renderCitation(CitationInlineNode $token, string $span, RenderContext $context): string
    {
        $citationTarget = $context->getMetas()->getCitationTarget($token->getName());
        if ($citationTarget === null) {
            $replacement = '[' . $token->getName() . ']';
        } else {
            $replacement = $this->citation($citationTarget, $context);
        }

        return str_replace(
            $token->getId(),
            $replacement,
            $span,
        );
    }

    private function renderFootnote(FootnoteInlineNode $token, string $span, RenderContext $context): string
    {
        if ($token->getNumber() > 0) {
            $footnoteTarget = $context->getMetas()->getFootnoteTarget($token->getNumber());
        } elseif ($token->getName() !== '') {
            $footnoteTarget = $context->getMetas()->getFootnoteTargetByName($token->getName());
        } else {
            $footnoteTarget = $context->getMetas()->getFootnoteTargetAnonymous();
        }

        if ($footnoteTarget === null) {
            $replacement = '[' . $token->getNumber() . ']';
        } else {
            $replacement = $this->footnote($footnoteTarget, $context);
        }

        return str_replace(
            $token->getId(),
            $replacement,
            $span,
        );
    }

    private function renderLiteral(LiteralToken $token, string $span, RenderContext $context): string
    {
        return str_replace(
            $token->getId(),
            $this->literal($token, $context),
            $span,
        );
    }

    private function renderLinkToken(AbstractLinkToken $spanToken, string $span, RenderContext $context): string
    {
        $link = $this->linkToken($spanToken, $context);

        return str_replace($spanToken->getId(), $link, $span);
    }

    private function renderLink(HyperLinkNode $spanToken, string $span, RenderContext $context): string
    {
        $url = $spanToken->getUrl();
        $link = $spanToken->getLink();

        if ($url === '') {
            $url = $context->getLink($link);

            if ($url === '') {
                //TODO: figure out how to refactor this, currently this seems to be some self document reference. But
                // Those should not be handled by this class, it's part of te resolving and stuff to do local
                // link resolving. Other sections can be linked

                $metaEntry = $context->getMetaEntry();

                if ($metaEntry !== null) {
                    $url = $context->relativeDocUrl(
                        $metaEntry->getFile(),
                        (new AsciiSlugger())->slug($link)->lower()->toString(),
                    );
                }
            }

            if ($url === '') {
                $this->logger->error(sprintf('Invalid link: %s', $link));

                return str_replace($spanToken->getId(), $link, $span);
            }
        }

        $link = $this->link($context, $url, $this->renderSyntaxes($link, $context));

        return str_replace($spanToken->getId(), $link, $span);
    }
}
