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

use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use phpDocumentor\Guides\Markdown\ParserInterface;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;

/**
 * @template TValue as InlineNodeInterface
 * @implements ParserInterface<TValue>
 */
abstract class AbstractInlineParser implements ParserInterface
{
    abstract public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): InlineNodeInterface;
}
