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

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Node\Node as CommonMarkNode;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use Psr\Log\LoggerInterface;

/** @extends AbstractInlineTextDecoratorParser<EmphasisInlineNode> */
final class EmphasisParser extends AbstractInlineTextDecoratorParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        iterable $inlineParsers,
        LoggerInterface $logger,
    ) {
        parent::__construct($inlineParsers, $logger);
    }

    protected function getType(): string
    {
        return 'Emphasis';
    }

    /** @param InlineNodeInterface[] $children */
    protected function createInlineNode(CommonMarkNode $commonMarkNode, string|null $content, array $children = []): InlineNodeInterface
    {
        return new EmphasisInlineNode($content ? [new PlainTextInlineNode($content)] : $children);
    }

    protected function supportsCommonMarkNode(CommonMarkNode $commonMarkNode): bool
    {
        return $commonMarkNode instanceof Emphasis;
    }
}
