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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

final class DocumentRule implements Rule
{
    private RuleContainer $productions;

    /**
     * @param iterable<DirectiveHandler> $directiveHandlers
     */
    public function __construct(iterable $directiveHandlers)
    {

        $spanParser = new SpanParser();
        $literalBlockRule = new LiteralBlockRule();
        $transitionRule = new TransitionRule(); // Transition rule must follow Title rule

        $inlineMarkupRule = new InlineMarkupRule($spanParser);
        // TODO: Somehow move this into the top of the instantiation chain so that you can configure which rules
        //       to use when consuming this library
        //
        // TODO, these productions are now used in sections and documentrule,
        //    however most of them do not apply on documents?
        //
        $productions = new RuleContainer(
            $transitionRule,
            new LinkRule(),
            $literalBlockRule,
            new BlockQuoteRule(),
            new ListRule(),
            new DirectiveRule($literalBlockRule, $directiveHandlers),
            new CommentRule(),
            new DefinitionListRule($spanParser),
            new TableRule(),
            // For now: ParagraphRule must be last as it is the rule that applies if none other applies.
            new ParagraphRule($inlineMarkupRule)
        );

        $this->productions = (new RuleContainer(new SectionRule(new TitleRule($spanParser), $productions)))
            ->merge($productions);
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
            $this->productions->apply($documentParserContext, $on);
        }

        return $on;
    }
}
