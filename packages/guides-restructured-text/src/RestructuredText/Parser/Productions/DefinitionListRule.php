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

use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionList;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListTerm;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

use function strpos;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#definition-lists
 */
final class DefinitionListRule implements Rule
{
    private SpanParser $spanParser;

    public function __construct(SpanParser $spanParser)
    {
        $this->spanParser = $spanParser;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isDefinitionList($documentParser->getDocumentIterator()->getNextLine());
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $buffer = new Buffer();

        while ($documentIterator->getNextLine() !== null
            && $this->isDefinitionListEnded($documentIterator->current(), $documentIterator->getNextLine()) === false
        ) {
            $buffer->push($documentIterator->current());
            $documentIterator->next();
        }

        // TODO: This is a workaround because the current Main Loop in {@see DocumentParser::parseLines()} expects
        //       the cursor position to rest at the last unprocessed line, but the logic above needs is always a step
        //       'too late' in detecting whether it should have stopped
        $documentIterator->prev();

        $definitionList = $this->parseDefinitionList($documentParserContext, $buffer->getLines());

        return new DefinitionListNode($definitionList);
    }

    private function isDefinitionList(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        return strpos($line, '    ') === 0;
    }

    private function isDefinitionListEnded(string $line, ?string $nextLine): bool
    {
        if (trim($line) === '') {
            return false;
        }

        if ($this->isDefinitionList($line)) {
            return false;
        }

        return !$this->isDefinitionList($nextLine);
    }


    /**
     * @param string[] $lines
     */
    private function parseDefinitionList(DocumentParserContext $documentParserContext, array $lines): DefinitionList
    {
        /** @var array{term: SpanNode, classifiers: list<SpanNode>, definition: string}|null $definitionListTerm */
        $definitionListTerm = null;
        $definitionList     = [];

        $createDefinitionTerm = function (array $definitionListTerm) use ($documentParserContext): ?DefinitionListTerm {
            // parse any markup in the definition (e.g. lists, directives)
            $definitionNodes = $documentParserContext->getParser()->parseFragment(
                $documentParserContext,
                $definitionListTerm['definition']
            )->getNodes();
            if (empty($definitionNodes)) {
                return null;
            } elseif (count($definitionNodes) === 1 && $definitionNodes[0] instanceof ParagraphNode) {
                // if there is only one paragraph node, the value is put directly in the <dd> element
                $definitionNodes = [$definitionNodes[0]->getValue()];
            } else {
                // otherwise, .first and .last are added to the first and last nodes of the definition
                $definitionNodes[0]->setClasses($definitionNodes[0]->getClasses() + ['first']);
                $definitionNodes[count($definitionNodes) - 1]
                    ->setClasses($definitionNodes[count($definitionNodes) - 1]->getClasses() + ['last']);
            }

            return new DefinitionListTerm(
                $definitionListTerm['term'],
                $definitionListTerm['classifiers'],
                $definitionNodes
            );
        };

        $currentOffset = 0;
        foreach ($lines as $key => $line) {
            // indent or empty line = term definition line
            if ($definitionListTerm !== null && (trim($line) === '') || $line[0] === ' ') {
                if ($currentOffset === 0) {
                    // first line of a definition determines the indentation offset
                    $definition    = ltrim($line);
                    $currentOffset = strlen($line) - strlen($definition);
                } else {
                    $definition = substr($line, $currentOffset);
                }

                $definitionListTerm['definition'] .= $definition . "\n";

                // non empty string at the start of the line = definition term
            } elseif (trim($line) !== '') {
                // we are starting a new term so if we have an existing
                // term with definitions, add it to the definition list
                if ($definitionListTerm !== null) {
                    $definitionList[] = $createDefinitionTerm($definitionListTerm);
                }

                $parts = explode(':', trim($line));

                $term = $parts[0];
                unset($parts[0]);

                $classifiers = array_map(function (string $classifier) use ($documentParserContext): SpanNode {
                    return $this->spanParser->parse($classifier, $documentParserContext->getContext());
                }, array_map('trim', $parts));

                $currentOffset      = 0;
                $definitionListTerm = [
                    'term' => $this->spanParser->parse($term, $documentParserContext->getContext()),
                    'classifiers' => $classifiers,
                    'definition' => '',
                ];
            }
        }

        // append the last definition of the list
        if ($definitionListTerm !== null) {
            $definitionList[] = $createDefinitionTerm($definitionListTerm);
        }

        return new DefinitionList($definitionList);
    }
}
