<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\RawNode;

final class EnumeratedListRuleTest extends AbstractRuleTest
{
    private EnumeratedListRule $rule;

    protected function setUp(): void
    {
        $ruleContainer = $this->givenCollectAllRuleContainer();
        $this->rule = new EnumeratedListRule($ruleContainer);
    }

    /** @dataProvider startChars */
    public function testAppliesForAllPossibleStartChars(string $char): void
    {
        $input = $char . ' test';

        $context = $this->createContext($input);

        self::assertTrue($this->rule->applies($context));
    }

    /** @dataProvider startChars */
    public function testListItemContentCanBeOnANewLine(string $char): void
    {
        $input = <<<INPUT
$char
  test
INPUT;

        $context = $this->createContext($input);

        self::assertTrue($this->rule->applies($context));
    }

    /** @return iterable<string, non-empty-list<string>> */
    public function startChars(): iterable
    {
        $chars = [
            '#',
            '1',
            'I',
            'i',
            'a',
            'A',
        ];

        foreach (['.', ')'] as $next) {
            foreach ($chars as $char) {
                yield $char . $next => [$char . $next];
            }
        }

        foreach ($chars as $char) {
            yield '(' . $char . ')' => ['(' . $char . ')'];
        }
    }

    public function testListDoesNotExceptEnumerated(): void
    {
        $input = 'A. Einstein was a really' . "\n" .
        'smart dude.';
        $context = $this->createContext($input);

        self::assertFalse($this->rule->applies($context));
    }

    public function testListItemMustBeIntended(): void
    {
        $input = <<<INPUT
a.
test
INPUT;

        $context = $this->createContext($input);

        self::assertFalse($this->rule->applies($context));
    }

    public function testSimpleListCreation(): void
    {
        $input = <<<INPUT
1. first items
2. second item

Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<REST
Not included

REST,
            $context->getDocumentIterator()
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('1', false, [new RawNode('first items')]),
                    new ListItemNode('2', false, [new RawNode('second item')]),
                ],
                true
            ),
            $result
        );
    }

    public function testListWithoutNewLineInParagraphResultsInWarning(): void
    {
        $input = <<<INPUT
1. first items
2. second item
Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<REST
Not included

REST,
            $context->getDocumentIterator()
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('1', false, [new RawNode('first items')]),
                    new ListItemNode('2', false, [new RawNode('second item')]),
                ],
                true
            ),
            $result
        );
    }

    public function testListFistTekstOnNewLine(): void
    {
        $input = <<<INPUT
(#)
  first items
(#)
  second item
  other line

Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<REST
Not included

REST,
            $context->getDocumentIterator()
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('#', false, [new RawNode('first items')]),
                    new ListItemNode('#', false, [new RawNode("second item\nother line")]),
                ],
                true
            ),
            $result
        );
    }

    public function testListWithOddIndenting(): void
    {
        $input = <<<INPUT
1. 
  first items
2. 
    second item
    other line
  Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<REST
  Not included

REST,
            $context->getDocumentIterator()
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('1', false, [new RawNode('first items')]),
                    new ListItemNode('2', false, [new RawNode("second item\nother line")]),
                ],
                true
            ),
            $result
        );
    }

    public function testListShouldCheckTheNextLineToBeValid(): void
    {
        $input = <<<INPUT
1. first items
2) second item
(#) Not included
INPUT;

        $context = $this->createContext($input);

        self::assertFalse($this->rule->applies($context));
    }
}
