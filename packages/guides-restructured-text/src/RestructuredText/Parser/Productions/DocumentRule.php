<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use InvalidArgumentException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionEndNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

use function array_search;

final class DocumentRule implements Rule
{
    /** @var Rule[] */
    private array $productions;

    /**
     * @param DirectiveHandler[] $directiveHandlers
     */
    public function __construct(
        MarkupLanguageParser  $parser,
        array                 $directiveHandlers
    ) {

        $spanParser = new SpanParser();
        $lineDataParser = new LineDataParser($parser, $spanParser);

        $literalBlockRule = new LiteralBlockRule();

        // TODO: Somehow move this into the top of the instantiation chain so that you can configure which rules
        //       to use when consuming this library
        $this->productions = [
            new TitleRule($parser, $spanParser),
            new TransitionRule(), // Transition rule must follow Title rule
            new LinkRule($lineDataParser, $parser),
            $literalBlockRule,
            new BlockQuoteRule($parser),
            new ListRule($parser),
            new DirectiveRule($parser, $lineDataParser, $literalBlockRule, $directiveHandlers),
            new CommentRule(),
            new DefinitionListRule($lineDataParser),
            new TableRule($parser),

            // For now: ParagraphRule must be last as it is the rule that applies if none other applies.
            new ParagraphRule($parser, $spanParser),
        ];
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $documentParser->getDocumentIterator()->atStart();
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        if ($on instanceof DocumentNode === false) {
            throw new InvalidArgumentException('Expected a document to apply this compound rule on');
        }

        $documentParserContext->lastTitleNode = null;
        $documentParserContext->openSectionsAsTitleNodes->exchangeArray([]); // clear it
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
                    $on->addNode($newNode);
                }

                break;
            }

            $documentIterator->next();
        }

        // TODO: Can we get rid of this here? It would make this parser cleaner and if it is part of the
        //       Title/SectionRule itself it is neatly encapsulated.
        foreach ($documentParserContext->openSectionsAsTitleNodes as $titleNode) {
            $this->endOpenSection($documentParserContext, $on, $titleNode);
        }

        return $on;
    }

    public function endOpenSection(DocumentParserContext $documentParserContext, DocumentNode $document, TitleNode $titleNode): void
    {
        $document->addNode(new SectionEndNode($titleNode));

        $key = array_search($titleNode, $documentParserContext->openSectionsAsTitleNodes->getArrayCopy(), true);

        if ($key === false) {
            return;
        }

        unset($documentParserContext->openSectionsAsTitleNodes[$key]);
    }
}
