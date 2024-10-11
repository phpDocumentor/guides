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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\RawNode;
use Psr\Log\LoggerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

use function assert;

/** @implements NodeTransformer<Node> */
final class RawNodeEscapeTransformer implements NodeTransformer
{
    private HtmlSanitizer $htmlSanitizer;

    public function __construct(
        private readonly bool $escapeRawNodes,
        private readonly LoggerInterface $logger,
        HtmlSanitizerConfig $htmlSanitizerConfig,
    ) {
        $this->htmlSanitizer = new HtmlSanitizer($htmlSanitizerConfig);
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        assert($node instanceof RawNode);
        if ($this->escapeRawNodes) {
            $this->logger->warning('We do not support plain HTML for security reasons. Escaping all HTML ');

            return new ParagraphNode([new InlineCompoundNode([new PlainTextInlineNode($node->getValue())])]);
        }

        if ($node->getOption('format', 'html') === 'html') {
            return new RawNode($this->htmlSanitizer->sanitize($node->getValue()));
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof RawNode;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
