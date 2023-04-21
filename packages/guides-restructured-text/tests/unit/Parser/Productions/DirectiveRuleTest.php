<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\RestructuredText\Directives\CodeBlock;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Parser\DummyDirective;
use phpDocumentor\Guides\RestructuredText\Parser\DummyNode;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_values;

final class DirectiveRuleTest extends RuleTestCase
{
    private DirectiveRule $rule;
    private DirectiveHandler $directiveHandler;

    public function setUp(): void
    {
        $this->directiveHandler = new DummyDirective();
        $this->rule = new DirectiveRule([$this->directiveHandler]);
    }

    #[DataProvider('simpleDirectiveProvider')]
    public function testApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    #[DataProvider('simpleNonDirectiveProvider')]
    public function testNotApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertFalse($this->rule->applies($context));
    }

    public function testApply(): void
    {
        $context = $this->createContext('.. dummy:: data');
        self::assertInstanceOf(DummyNode::class, $this->rule->apply($context));
    }

    public function testApplySetsEmptyOptionTrue(): void
    {
        $context = $this->createContext(<<<'NOWDOC'
.. dummy:: data
    :option: 
NOWDOC);
        $node = $this->rule->apply($context);
        self::assertInstanceOf(DummyNode::class, $node);
        self::assertCount(1, $node->getDirectiveOptions());
        self::assertEquals('option', array_values($node->getDirectiveOptions())[0]->getName());
        self::assertTrue(array_values($node->getDirectiveOptions())[0]->getValue());
    }

    public function testApplySetsOptionValue(): void
    {
        $context = $this->createContext(<<<'NOWDOC'
.. dummy:: data
    :option: value
NOWDOC);
        $node = $this->rule->apply($context);
        self::assertInstanceOf(DummyNode::class, $node);
        self::assertCount(1, $node->getDirectiveOptions());
        self::assertEquals('option', array_values($node->getDirectiveOptions())[0]->getName());
        self::assertEquals('value', array_values($node->getDirectiveOptions())[0]->getValue());
    }

    public function testApplySetsOptionValueMultipleLines(): void
    {
        $context = $this->createContext(<<<'NOWDOC'
.. dummy:: data
    :option: some very long option
      in multiple, very long,
      lines
NOWDOC);
        $node = $this->rule->apply($context);
        self::assertInstanceOf(DummyNode::class, $node);
        self::assertCount(1, $node->getDirectiveOptions());
        self::assertEquals('option', array_values($node->getDirectiveOptions())[0]->getName());
        self::assertEquals(
            'some very long option in multiple, very long, lines',
            array_values($node->getDirectiveOptions())[0]->getValue(),
        );
    }

    #[DataProvider('codeBlockValueProvider')]
    public function testCodeBlockValue(string $input, string $expectedValue): void
    {
        $this->rule = new DirectiveRule([$this->directiveHandler, new CodeBlock()]);
        $context = $this->createContext($input);
        $node = $this->rule->apply($context);
        self::assertInstanceOf(CodeNode::class, $node);
        self::assertEquals($expectedValue, $node->getValue());
    }

    /** @return array<int, array<int, string>> */
    public static function codeBlockValueProvider(): array
    {
        return [
            [
                <<<'INPUT'
.. code-block::

    Whitespace, newlines, blank lines, and all kinds of markup
      (like *this* or \this) is preserved by literal blocks.
  Lookie here, I've dropped an indentation level
  (but not far enough)

This is outside the code-block
INPUT,
                <<<'EXPECTED'
  Whitespace, newlines, blank lines, and all kinds of markup
    (like *this* or \this) is preserved by literal blocks.
Lookie here, I've dropped an indentation level
(but not far enough)
EXPECTED,
            ],
        ];
    }

    /** @return array<array<string>> */
    public static function simpleDirectiveProvider(): array
    {
        return [
            ['.. name::'],
            ['..  name::'],
            ['.. name:: data'],
            ['.. multi:part:name:: data'],
            ['.. abc123-b_c+d:e.f:: data'],
            ['.. |variable| name:: data'],
        ];
    }

    /** @return array<array<string>> */
    public static function simpleNonDirectiveProvider(): array
    {
        return [
            [''],
            ['..name::'],
            [':field-option:'],
            ['... name:: data'],
            ['.. name: data'],
            ['.. multi part name:: data'],
        ];
    }
}
