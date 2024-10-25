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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\NewlineInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\UnindentStrategy;

use function ltrim;
use function mb_strlen;
use function str_starts_with;
use function substr;

/** @implements Rule<ContainerNode> */
final class LineBlockRule implements Rule
{
    public function __construct(
        private InlineMarkupRule $inlineMarkupRule,
    ) {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return str_starts_with($blockContext->getDocumentIterator()->current(), '| ')
            && $blockContext->getDocumentIterator()->getNextLine() !== null
            && str_starts_with($blockContext->getDocumentIterator()->getNextLine(), '| ');
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        return $this->createLineBlock($blockContext);
    }

    private function collectContentLines(BlockContext $blockContext): Buffer
    {
        $buffer = new Buffer(
            [
                substr($blockContext->getDocumentIterator()->current(), 2),
            ],
            UnindentStrategy::NONE,
        );

        $blockContext->getDocumentIterator()->next();

        while (
            $blockContext->getDocumentIterator()->valid()
            && LinesIterator::isEmptyLine($blockContext->getDocumentIterator()->current()) === false
            && str_starts_with($blockContext->getDocumentIterator()->current(), '|') === false
        ) {
            $buffer->push($blockContext->getDocumentIterator()->current());
            $blockContext->getDocumentIterator()->next();
        }

        return $buffer;
    }

    /** @return CompoundNode<InlineNodeInterface> */
    private function createLine(BlockContext $blockContext, Buffer $buffer): CompoundNode
    {
        $line = $this->inlineMarkupRule->apply(new BlockContext(
            $blockContext->getDocumentParserContext(),
            $buffer->getLinesString(),
            true,
        ));

        if ($line->getChildren() === []) {
            $line->addChildNode(new NewlineInlineNode());
        }

        return $line;
    }

    private function createLineBlock(BlockContext $blockContext, int $indent = 0): ContainerNode
    {
        $lineBlock = new ContainerNode();
        $lineBlock->setClasses(['line-block']);

        while (
            $blockContext->getDocumentIterator()->valid()
            && LinesIterator::isEmptyLine($blockContext->getDocumentIterator()->current()) === false
            && ($indent === 0 || LinesIterator::isIndented(substr($blockContext->getDocumentIterator()->current(), 2), $indent))
        ) {
            if (LinesIterator::isIndented(substr($blockContext->getDocumentIterator()->current(), 2), $indent + 1)) {
                $line = substr($blockContext->getDocumentIterator()->current(), 2);
                $lineBlock->addChildNode(
                    $this->createLineBlock($blockContext, mb_strlen($line) - mb_strlen(ltrim($line))),
                );
                continue;
            }

            $child = new ContainerNode();
            $child->setClasses(['line']);
            $buffer = $this->collectContentLines($blockContext);
            $child->addChildNode($this->createLine($blockContext, $buffer));
            $lineBlock->addChildNode($child);
        }

        return $lineBlock;
    }
}
