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

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CodeNode;

use function assert;
use function explode;

/** @extends AbstractBlockParser<CodeNode> */
final class CodeBlockParser extends AbstractBlockParser
{
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): CodeNode
    {
        assert($current instanceof IndentedCode || $current instanceof FencedCode);
        $walker->next();
        $codeNode = new CodeNode(explode("\n", $current->getLiteral()));
        if ($current instanceof FencedCode && $current->getInfo() !== null) {
            $codeNode = $codeNode->withOptions(['caption' => $current->getInfo()]);
        }

        return $codeNode;
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->getNode() instanceof IndentedCode || $event->getNode() instanceof FencedCode;
    }
}
