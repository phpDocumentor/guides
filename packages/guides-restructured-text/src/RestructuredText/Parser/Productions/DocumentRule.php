<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

final class DocumentRule implements Rule
{
    /** @var Rule[] */
    private array $productions;

    /**
     * @param iterable<DirectiveHandler> $directiveHandlers
     */
    public function __construct(iterable $directiveHandlers)
    {

        $spanParser = new SpanParser();
        $lineDataParser = new LineDataParser($spanParser);

        $literalBlockRule = new LiteralBlockRule();
        $transitionRule = new TransitionRule(); // Transition rule must follow Title rule

        // TODO: Somehow move this into the top of the instantiation chain so that you can configure which rules
        //       to use when consuming this library
        //
        // TODO, these productions are now used in sections and documentrule,
        //    however most of them do not apply on documents?
        //
        $productions = [
            $transitionRule,
            new LinkRule($lineDataParser),
            $literalBlockRule,
            new BlockQuoteRule(),
            new ListRule(),
            new DirectiveRule($lineDataParser, $literalBlockRule, $directiveHandlers),
            new CommentRule(),
            new DefinitionListRule($lineDataParser),
            new TableRule($lineDataParser),

            // For now: ParagraphRule must be last as it is the rule that applies if none other applies.
            new ParagraphRule($spanParser),
        ];

        $this->productions = array_merge([
            new SectionRule(new TitleRule($spanParser), $productions),
        ], $productions);
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $documentParser->getDocumentIterator()->atStart();
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $on = $on ?? new DocumentNode(
            md5(implode("\n", $documentParserContext->getDocumentIterator()->toArray())),
            $documentParserContext->getContext()->getCurrentFileName()
        );

        $documentParserContext->setDocument($on);
        $documentIterator = $documentParserContext->getDocumentIterator();

        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            foreach ($this->productions as $production) {
                if (!$production->applies($documentParserContext)) {
                    continue;
                }

                $newNode = $production->apply($documentParserContext, $on);
                if ($newNode !== null) {
                    $on->addChildNode($newNode);
                }

                break;
            }

            $documentIterator->next();
        }

        return $on;
    }
}
