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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\References\ReferenceResolver;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\Span\CrossReferenceNode;
use phpDocumentor\Guides\Span\LiteralToken;
use phpDocumentor\Guides\Span\SpanToken;
use phpDocumentor\Guides\UrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function assert;
use function is_string;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_replace;

/** @implements NodeRenderer<SpanNode> */
abstract class SpanNodeRenderer implements NodeRenderer, SpanRenderer, NodeRendererFactoryAware
{
    /** @var Renderer */
    protected $renderer;

    private ?NodeRendererFactory $nodeRendererFactory = null;

    private ReferenceResolver $referenceResolver;

    private LoggerInterface $logger;
    protected UrlGenerator $urlGenerator;

    public function __construct(
        Renderer $renderer,
        ReferenceResolver $referenceResolver,
        LoggerInterface $logger,
        UrlGenerator $urlGenerator
    ) {
        $this->renderer = $renderer;
        $this->referenceResolver = $referenceResolver;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
    }

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        if ($node instanceof SpanNode === false) {
            throw new InvalidArgumentException('Invalid node presented');
        }

        $value = $node->getValueString();

        $span = $this->renderSyntaxes($value, $environment);
        return $this->renderTokens($node, $span, $environment);
    }

    /**
     * @param string[] $attributes
     * @param string|TitleNode $title
     */
    public function link(RenderContext $environment, ?string $url, $title, array $attributes = []): string
    {
        $url = (string) $url;

        return $this->renderer->render(
            'link.html.twig',
            [
                'url' => $this->urlGenerator->generateUrl($url),
                'title' => $title,
                'attributes' => $attributes,
            ]
        );
    }

    private function renderSyntaxes(string $span, RenderContext $environment): string
    {
        $span = $this->escape($span);

        $span = $this->renderStrongEmphasis($span);

        $span = $this->renderEmphasis($span);

        $span = $this->renderNbsp($span);

        $span = $this->renderVariables($span, $environment);

        return $this->renderBrs($span);
    }

    private function renderStrongEmphasis(string $span): string
    {
        return preg_replace_callback(
            '/\*\*(.+)\*\*/mUsi',
            fn(array $matches): string => $this->strongEmphasis($matches[1]),
            $span
        );
    }

    private function renderEmphasis(string $span): string
    {
        return preg_replace_callback(
            '/\*(.+)\*/mUsi',
            fn(array $matches): string => $this->emphasis($matches[1]),
            $span
        );
    }

    private function renderNbsp(string $span): string
    {
        return preg_replace('/~/', $this->nbsp(), $span);
    }

    private function renderVariables(string $span, RenderContext $context): string
    {
        return preg_replace_callback(
            '/\|(.+)\|/mUsi',
            function (array $match) use ($context): string {
                $variable = $context->getVariable($match[1]);

                if ($variable === null) {
                    return '';
                }

                if ($variable instanceof Node) {
                    assert($this->nodeRendererFactory !== null);
                    return $this->nodeRendererFactory->get($variable)->render($variable, $context);
                }

                if (is_string($variable)) {
                    return $variable;
                }

                return (string) $variable;
            },
            $span
        );
    }

    private function renderBrs(string $span): string
    {
        // Adding brs when a space is at the end of a line
        return preg_replace('/ \n/', $this->br(), $span);
    }

    private function renderTokens(SpanNode $node, string $span, RenderContext $context): string
    {
        foreach ($node->getTokens() as $token) {
            if ($token instanceof CrossReferenceNode) {
                $reference = $this->referenceResolver->resolve($token, $context);

                if ($reference === null) {
                    $this->logger->error(sprintf('Invalid cross reference: %s', $token->getUrl()));

                    $span = str_replace($token->getId(), $token->getText(), $span);
                    continue;
                }

                $span = str_replace(
                    $token->getId(),
                    $this->link($context, $reference->getUrl(), $reference->getTitle(), $reference->getAttributes()),
                    $span
                );

                continue;
            }

            $span = $this->renderToken($token, $span, $context);
        }

        return $span;
    }

    private function renderToken(SpanToken $spanToken, string $span, RenderContext $context): string
    {
        switch ($spanToken->getType()) {
            case SpanToken::TYPE_LITERAL:
                assert($spanToken instanceof LiteralToken);

                return $this->renderLiteral($spanToken, $span);

            case SpanToken::TYPE_LINK:
                return $this->renderLink($spanToken, $span, $context);
        }

        throw new InvalidArgumentException(sprintf('Unknown token type %s', $spanToken->getType()));
    }

    private function renderLiteral(LiteralToken $token, string $span): string
    {
        return str_replace(
            $token->getId(),
            $this->literal($token),
            $span
        );
    }

    private function renderLink(SpanToken $spanToken, string $span, RenderContext $context): string
    {
        $url = $spanToken->get('url');
        $link = $spanToken->get('link');

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
                        (new AsciiSlugger())->slug($link)->lower()->toString()
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
