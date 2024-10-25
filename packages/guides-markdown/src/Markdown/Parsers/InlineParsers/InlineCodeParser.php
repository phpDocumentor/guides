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

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;

use function assert;

/** @extends AbstractInlineParser<LiteralInlineNode> */
final class InlineCodeParser extends AbstractInlineParser
{
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): InlineNodeInterface
    {
        assert($current instanceof Code);

        return new LiteralInlineNode($current->getLiteral());
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->getNode() instanceof Code;
    }
}
