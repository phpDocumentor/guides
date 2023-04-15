<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldNode;
use phpDocumentor\Guides\Nodes\RawNode;

final class FieldListRuleTest extends AbstractRuleTest
{
    private FieldListRule $rule;

    protected function setUp(): void
    {
        $this->rule = new FieldListRule();
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

    /** @dataProvider fieldListApplicationProvider */
    public function testApply(string $input, ?FieldListNode $expected, ?string $nextLine): void
    {
        $context = $this->createContext($input);


        $result = $this->rule->apply($context);

        self::assertEquals($expected, $result);
        self::assertRemainingEquals($nextLine ?? '', $context->getDocumentIterator());
    }

    /** @return array<string, mixed[]> */
    public function fieldListApplicationProvider(): array
    {
        return [
            'testEmptyFieldList' => [
                <<<RST
:term 1:

This is normal text again.

RST,
                new FieldListNode(
                    new FieldListItemNode(
                        'term 1',
                        []
                    ),
                ),
                'This is normal text again.' . "\n",
            ],
            'test3EmptyFieldList' => [
                <<<RST
:term 1:
:term 2:
:term 3:

This is normal text again.

RST,
                new FieldListNode(
                    new FieldListItemNode(
                        'term 1',
                        []
                    ),
                    new FieldListItemNode(
                        'term 2',
                        []
                    ),
                    new FieldListItemNode(
                        'term 3',
                        []
                    ),
                ),
                'This is normal text again.' . "\n",
            ],
            'testFieldWithDirectContent' => [
                <<<RST
:term 1: content 1

This is normal text again.

RST,
                new FieldListNode(
                    new FieldListItemNode(
                        'term 1',
                        [
                            new FieldNode(
                                [
                                    new RawNode('content 1'),
                                ]
                            ),
                        ]
                    ),
                ),
                'This is normal text again.' . "\n",
            ],
        ];
    }

    /** @return array<string, string[]> */
    public function definitionListProvider(): array
    {
        return [
            'Empty field' => [':empty:'],
            'Field with value' => [':field: Value'],
            'Whitespace in Fieldname' => [':field with whitespace: Some values'],
        ];
    }

    /** @return array<string, string[]> */
    public function isDefinitionListFalseProvider(): array
    {
        return [
            'empty lines' => [''],
            'directive' => ['.. directive:: something'],
            'escaped colons' => ['\:escaped\:'],
        ];
    }
}
