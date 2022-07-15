<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

final class SectionRule implements Rule
{
    private TitleRule $titleRule;
    private DocumentParserContext $documentParser;

    /** @var Rule[] */
    private array $productions;

    /** @param Rule[] $productions */
    public function __construct(TitleRule $titleRule, DocumentParserContext $documentParser, array $productions)
    {
        $this->titleRule = $titleRule;
        $this->documentParser = $documentParser;
        $this->productions = $productions;
        $this->productions[] = $this;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->titleRule->applies($documentParser);
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        //First time we enter this, title level will be null.
        // $on will be a DocumentNode.

        // If we detect a title, check the level:
        //If title level is same as current level, inject in on
        //if title level is deeper than create a new section.
        //If title level is smaller than current, we need to return,
        //    as a new section should be create on or more levels up.

        while ($documentParserContext->valid()) {
            $on->addNode($this->createSection($documentParserContext));
        }

        return null;
    }

    private function createSection(LinesIterator $documentIterator)
    {
        $title = $this->titleRule->apply($documentIterator);
        $section = new SectionNode($title);
        $sections = [];

        while ($documentIterator->valid()) {
            if ($this->applies($this->documentParser)) {
//                $sections[] = $section;
//                $title = $this->titleRule->apply($documentIterator);
//                $section = new SectionNode($title);

                return $section;
            }

            foreach ($this->productions as $production) {
                if (!$production->applies($this->documentParser)) {
                    continue;
                }

                $newNode = $production->apply($documentIterator, $section);
                if ($newNode !== null) {
                    $section->addNode($newNode);
                }

                break;
            }

            $documentIterator->next();
        }

        //Looks like we need some recursive stuff in here.
        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        return $section;
    }
}
