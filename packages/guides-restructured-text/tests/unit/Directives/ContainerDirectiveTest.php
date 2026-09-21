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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use PHPUnit\Framework\TestCase;

final class ContainerDirectiveTest extends TestCase
{
    private TestHandler $logHandler;
    private ContainerDirective $directive;

    protected function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->directive = new ContainerDirective($this->createStub(Rule::class), $logger);
    }

    public function testLangOptionIsAppliedToTheNode(): void
    {
        $directive = new Directive('', 'container', '', ['lang' => new DirectiveOption('lang', 'nb')]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertSame('nb', $node->getOption('lang'));
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testDirOptionIsAppliedToTheNode(): void
    {
        $directive = new Directive('', 'container', '', ['dir' => new DirectiveOption('dir', 'rtl')]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertSame('rtl', $node->getOption('dir'));
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testLangAndDirCanBeCombined(): void
    {
        $directive = new Directive('', 'container', 'greeting', [
            'lang' => new DirectiveOption('lang', 'ar'),
            'dir' => new DirectiveOption('dir', 'rtl'),
        ]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertSame('ar', $node->getOption('lang'));
        self::assertSame('rtl', $node->getOption('dir'));
        self::assertSame('greeting', $node->getOption('class'));
    }

    public function testNeitherOptionIsRequired(): void
    {
        $directive = new Directive('', 'container', 'my-class', []);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertNull($node->getOption('lang'));
        self::assertNull($node->getOption('dir'));
        self::assertSame('my-class', $node->getOption('class'));
    }

    public function testDirIsReadRegardlessOfCase(): void
    {
        $directive = new Directive('', 'container', '', ['dir' => new DirectiveOption('dir', 'RTL')]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertSame('rtl', $node->getOption('dir'));
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testInvalidDirWarnsAndFallsBackToAuto(): void
    {
        $directive = new Directive('', 'container', '', ['dir' => new DirectiveOption('dir', 'sideways')]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        // Rather than a "dir" attribute the browser cannot act on.
        self::assertSame('auto', $node->getOption('dir'));
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects one of "ltr", "rtl" or "auto", but was given "sideways"',
        ));
    }

    public function testInvalidLangWarnsButIsStillApplied(): void
    {
        $directive = new Directive('', 'container', '', ['lang' => new DirectiveOption('lang', 'not a language')]);

        $node = $this->directive->createNode(new DirectiveNode($directive));

        self::assertInstanceOf(ContainerNode::class, $node);
        self::assertSame('not a language', $node->getOption('lang'));
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "not a language"',
        ));
    }
}
