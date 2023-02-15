<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use SplStack;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Webmozart\Assert\Assert;

/**
 * @implements Rule<SectionNode>
 */
final class SectionRule implements Rule
{
    private TitleRule $titleRule;
    private RuleContainer $productions;

    public function __construct(TitleRule $titleRule, RuleContainer $productions)
    {
        $this->titleRule = $titleRule;
        $this->productions = $productions;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->titleRule->applies($documentParser);
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode $on = null): ?Node
    {
        /** @var SplStack<DocumentNode|SectionNode> $stack */
        $stack = new SplStack();
        $documentIterator = $documentParserContext->getDocumentIterator();
        $section = $this->createSection($documentParserContext);
        Assert::isInstanceOf($on, CompoundNode::class);
        $on->addChildNode($section);

        $stack->push($on);
        while ($documentIterator->valid()) {
            $this->fillSection($documentParserContext, $section);

            if ($documentIterator->getNextLine()) {
                $new = $this->createSection($documentParserContext);
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

                if ($new->getTitle()->getLevel() < $section->getTitle()->getLevel()) {
                    while ($new->getTitle()->getLevel() < $stack->top()->getTitle()->getLevel()) {
                        $stack->pop();
                    }

                    $stack->pop();
                    $stack->top()->addChildNode($new);
                    $section = $new;
                }
            }
        }

        return null;
    }

    private function fillSection(DocumentParserContext $documentParserContext, SectionNode $on): CompoundNode
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            if ($this->applies($documentParserContext)) {
                return $on;
            }

            $this->productions->apply($documentParserContext, $on);
        }

        return $on;
    }

    private function createSection(DocumentParserContext $documentParserContext): SectionNode
    {
        $title = $this->titleRule->apply($documentParserContext);
        return new SectionNode($title);
    }
}
