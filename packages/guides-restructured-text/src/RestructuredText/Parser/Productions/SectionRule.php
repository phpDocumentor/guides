<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use SplStack;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

final class SectionRule implements Rule
{
    private TitleRule $titleRule;

    /** @var Rule[] */
    private array $productions;

    /** @param Rule[] $productions */
    public function __construct(TitleRule $titleRule, array $productions)
    {
        $this->titleRule = $titleRule;
        $this->productions = $productions;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->titleRule->applies($documentParser);
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $stack = new SplStack();
        $documentIterator = $documentParserContext->getDocumentIterator();
        $section = $this->createSection($documentParserContext);
        $on->addNode($section);

        $stack->push($on);
        while ($documentIterator->valid()) {
            $this->fillSection($documentParserContext, $section);

            if ($documentIterator->getNextLine()) {
                $new = $this->createSection($documentParserContext);
                if ($new->getTitle()->getLevel() === $section->getTitle()->getLevel()) {
                    $stack->top()->addNode($new);
                    $section = $new;
                    continue;
                }

                if ($new->getTitle()->getLevel() > $section->getTitle()->getLevel()) {
                    $section->addNode($new);
                    $stack->push($section);
                    $section = $new;
                    continue;
                }

                if ($new->getTitle()->getLevel() < $section->getTitle()->getLevel()) {
                    while ($new->getTitle()->getLevel() < $stack->top()->getTitle()->getLevel()) {
                        $stack->pop();
                    }

                    $stack->pop();
                    $stack->top()->addNode($new);
                    $section = $new;
                }
            }
        }

        return null;
    }

    private function fillSection(DocumentParserContext $documentParserContext, SectionNode $on): void
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            if ($this->applies($documentParserContext)) {
                return;
            }

            foreach ($this->productions as $production) {
                if (!$production->applies($documentParserContext)) {
                    continue;
                }

                $newNode = $production->apply($documentParserContext, $on);
                if ($newNode !== null) {
                    $on->addNode($newNode);
                }

                break;
            }

            $documentIterator->next();
        }
    }

    private function createSection(DocumentParserContext $documentParserContext): SectionNode
    {
        $title = $this->titleRule->apply($documentParserContext);
        return new SectionNode($title);
    }
}
