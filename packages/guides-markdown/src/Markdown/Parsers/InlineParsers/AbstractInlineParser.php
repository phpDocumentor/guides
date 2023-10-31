<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use phpDocumentor\Guides\Markdown\ParserInterface;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;

/**
 * @template TValue as InlineNode
 * @implements ParserInterface<TValue>
 */
abstract class AbstractInlineParser implements ParserInterface
{
    abstract public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): InlineNode;
}
