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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use Psr\Log\LoggerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Renders a raw block, example:
 *
 * .. raw::
 *
 *      <u>Underlined!</u>
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#raw-data-pass-through
 */
#[Attributes\Directive(name: 'raw', rawContent: true)]
final class RawDirective extends BaseDirective
{
    private readonly HtmlSanitizer $htmlSanitizer;

    public function __construct(
        private readonly bool $escapeRawNodes,
        private readonly LoggerInterface $logger,
        HtmlSanitizerConfig $htmlSanitizerConfig,
    ) {
        $this->htmlSanitizer = new HtmlSanitizer($htmlSanitizerConfig);
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        $node = new RawNode(
            $directiveNode->getRawContent(),
            $directiveNode->getDirective()->getData(),
        );

        // Escaping/sanitizing here (rather than in a NodeTransformer) is required:
        // this directive's node is only created once DirectiveProcessPass resolves
        // it during compile, by which point transformers that ran earlier in the
        // pipeline (by priority) would never see the final RawNode to act on.
        if ($this->escapeRawNodes) {
            $this->logger->warning('We do not support plain HTML for security reasons. Escaping all HTML ');

            return new ParagraphNode([new InlineCompoundNode([new PlainTextInlineNode($node->getValue())])]);
        }

        if ($node->getOption('format', 'html') === 'html') {
            return new RawNode($this->htmlSanitizer->sanitize($node->getValue()));
        }

        return $node;
    }
}
