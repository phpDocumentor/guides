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

use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\RawNode;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;

final class ListRuleTest extends RuleTestCase
{
    private ListRule $rule;

    protected function setUp(): void
    {
        $ruleContainer = $this->givenCollectAllRuleContainer();
        $this->rule = new ListRule($ruleContainer, new NullLogger());
    }

    #[DataProvider('startChars')]
    public function testAppliesForAllPossibleStartChars(string $char): void
    {
        $input = $char . ' test';

        $context = $this->createContext($input);

        self::assertTrue($this->rule->applies($context));
    }

    /** @return string[][] */
    public static function startChars(): array
    {
        return [
            ['*'],
            ['+'],
            ['-'],
            ['•'],
            ['‣'],
            ['⁃'],
        ];
    }

    public function testListDoesNotExceptEnumerated(): void
    {
        $input = '1 test';
        $context = $this->createContext($input);

        self::assertFalse($this->rule->applies($context));
    }

    public function testListItemContentCanBeOnANewLine(): void
    {
        $input = <<<'INPUT'
-
  test
INPUT;

        $context = $this->createContext($input);

        self::assertTrue($this->rule->applies($context));
    }

    public function testListItemMustBeIntendedThisListIsAnEmptyList(): void
    {
        $input = <<<'INPUT'
-
test
INPUT;

        $context = $this->createContext($input);

        self::assertTrue($this->rule->applies($context));
    }

    public function testSimpleListCreation(): void
    {
        $input = <<<'INPUT'
- first items
- second item

Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<'REST'
Not included

REST,
            $context->getDocumentIterator(),
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('-', false, [new RawNode('first items')]),
                    new ListItemNode('-', false, [new RawNode('second item')]),
                ],
            ),
            $result,
        );
    }

    public function testListWithoutNewLineInParagraphResultsInWarning(): void
    {
        $input = <<<'INPUT'
- first items
- second item
Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<'REST'
Not included

REST,
            $context->getDocumentIterator(),
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('-', false, [new RawNode('first items')]),
                    new ListItemNode('-', false, [new RawNode('second item')]),
                ],
            ),
            $result,
        );
    }

    public function testListFistTekstOnNewLine(): void
    {
        $input = <<<'INPUT'
- 
  first items
- 
  second item
  other line

Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<'REST'
Not included

REST,
            $context->getDocumentIterator(),
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('-', false, [new RawNode('first items')]),
                    new ListItemNode('-', false, [new RawNode("second item\nother line")]),
                ],
            ),
            $result,
        );
    }

    public function testNestedList(): void
    {
        $input = <<<'INPUT'
* This is an item with sub items

  * A valid
  * subitem
  * list

INPUT;
        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('*', false, [
                        new RawNode('This is an item with sub items

* A valid
* subitem
* list'),
                    ]),
                ],
            ),
            $result,
        );
    }

    public function testListWithOddIndenting(): void
    {
        $input = <<<'INPUT'
- 
  first items
- 
    second item
    other line
  Not included
INPUT;

        $context = $this->createContext($input);

        $result = $this->rule->apply($context);

        self::assertRemainingEquals(
            <<<'REST'
  Not included

REST,
            $context->getDocumentIterator(),
        );

        self::assertEquals(
            new ListNode(
                [
                    new ListItemNode('-', false, [new RawNode('first items')]),
                    new ListItemNode('-', false, [new RawNode("second item\nother line")]),
                ],
            ),
            $result,
        );
    }
}
