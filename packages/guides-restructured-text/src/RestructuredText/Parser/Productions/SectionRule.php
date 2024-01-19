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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use SplStack;
use Webmozart\Assert\Assert;

/** @implements Rule<SectionNode> */
final class SectionRule implements Rule
{
    public const PRIORITY = 10;

    public function __construct(private readonly TitleRule $titleRule, private readonly RuleContainer $bodyElements)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return $this->titleRule->applies($blockContext);
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): SectionNode|null
    {
        /** @var SplStack<DocumentNode|SectionNode> $stack */
        $stack = new SplStack();
        $documentIterator = $blockContext->getDocumentIterator();
        $section = $this->createSection($blockContext);
        Assert::isInstanceOfAny($on, [DocumentNode::class, SectionNode::class]);
        $on->addChildNode($section);

        $stack->push($on);
        while ($documentIterator->valid()) {
            $this->fillSection($blockContext, $section);

            if (!$documentIterator->getNextLine()) {
                continue;
            }

            $new = $this->createSection($blockContext);
            if ($new->getTitle()->getLevel() === $section->getTitle()->getLevel()) {
                $stack->top()->addChildNode($new);
                $section = $new;
                continue;
            }

            if ($new->getTitle()->getLevel() > $section->getTitle()->getLevel()) {
                $section->addChildNode($new);
                $stack->push($section);
                $section = $new;
                continue;
            }

            if ($new->getTitle()->getLevel() >= $section->getTitle()->getLevel()) {
                continue;
            }

            while (
                $stack->top()->getTitle() !== null &&
                $new->getTitle()->getLevel() < $stack->top()->getTitle()->getLevel()
            ) {
                $stack->pop();
            }

            $stack->pop();
            $stack->top()->addChildNode($new);
            $section = $new;
        }

        return null;
    }

    private function fillSection(BlockContext $blockContext, SectionNode $on): SectionNode
    {
        $documentIterator = $blockContext->getDocumentIterator();
        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            if ($this->applies($blockContext)) {
                return $on;
            }

            $this->bodyElements->apply($blockContext, $on);
        }

        return $on;
    }

    private function createSection(BlockContext $blockContext): SectionNode
    {
        $title = $this->titleRule->apply($blockContext);
        Assert::isInstanceOf($title, TitleNode::class, 'Cannot create section without title');

        return new SectionNode($title);
    }
}
