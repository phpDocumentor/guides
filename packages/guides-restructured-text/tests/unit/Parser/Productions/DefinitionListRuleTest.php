<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SpanNode;

final class DefinitionListRuleTest extends AbstractRuleTest
{
    private DefinitionListRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DefinitionListRule($this->givenInlineMarkupRule(), $this->givenCollectAllRuleContainer());
    }

    /** @dataProvider definitionListProvider */
    public function testAppliesReturnsTrueOnValidInput(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    /** @dataProvider isDefinitionListFalseProvider */
    public function testAppliesReturnsFalseOnInvalidInput(string $input): void
    {
        $context = $this->createContext($input);
        self::assertFalse($this->rule->applies($context));
    }

    public function testParsDefinitionList(): void
    {
        $input = <<<RST
term 1
    Definition 1.

term 2
    Definition 2, paragraph 1.
    .. note::


        This is a note belongs to definition 2.

    Definition 2, paragraph 2.

term 3 : classifier
    Definition 3.

term 4 : classifier one : classifier two
    Definition 4.

\- term 5
    Without escaping, this would be an option list item.

This is normal text again.
RST;

        $context = $this->createContext($input);


        $result = $this->rule->apply($context);
        $expected = new DefinitionListNode(
            new DefinitionListItemNode(
                new SpanNode('term 1'),
                [],
                [
                    new DefinitionNode(
                        [
                            new RawNode('Definition 1.'),
                        ]
                    )
                ]
            ),
            new DefinitionListItemNode(
                new SpanNode('term 2'),
                [],
                [
                    new DefinitionNode(
                        [
                            new RawNode(<<<'RST'
Definition 2, paragraph 1.
.. note::


    This is a note belongs to definition 2.

Definition 2, paragraph 2.
RST
                        ),
                        ]
                    ),
                ]
            ),
            new DefinitionListItemNode(
                new SpanNode('term 3'),
                [
                    new SpanNode('classifier')
                ],
                [
                    new DefinitionNode(
                        [
                            new RawNode('Definition 3.'),
                        ]
                    ),
                ]
            ),
            new DefinitionListItemNode(
                new SpanNode('term 4'),
                [
                    new SpanNode('classifier one'),
                    new SpanNode('classifier two')
                ],
                [
                    new DefinitionNode(
                        [
                            new RawNode('Definition 4.'),
                        ]
                    ),
                ]
            ),
            new DefinitionListItemNode(
                new SpanNode('- term 5'),
                [],
                [
                    new DefinitionNode(
                        [
                            new RawNode('Without escaping, this would be an option list item.'),
                        ]
                    ),
                ]
            )
        );

        self::assertEquals($expected, $result);
        self::assertRemainingEquals('This is normal text again.' . "\n", $context->getDocumentIterator());
    }

    /** @return array<string, string[]> */
    public function definitionListProvider(): array
    {
        return [
            'line ending with colon and space' => ["Test:\n  Definition"],
            'line ending with newline' => ["Test\n  Definition"],
            'line ending with two spaces' => ["Test  \n  Definition"],
            'term with classifiers' => [<<<EOT
Term 1: classifier 1: classifier 2
  Definition
EOT],
            'multiple definitions' => [<<<EOT
Term 2: classifier 1
  Definition 1
  Definition 2
EOT],
        ];
    }

    /** @return array<string, string[]> */
    public function isDefinitionListFalseProvider(): array
    {
        return [
            'empty lines' => [""],
            'line ending with newline' => ["Test\n\n  Definition"],
            'Next line is not a block line' => ["Test\nDefinition"],
        ];
    }
}
