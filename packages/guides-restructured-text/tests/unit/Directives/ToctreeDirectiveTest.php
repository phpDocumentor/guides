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

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class ToctreeDirectiveTest extends TestCase
{
    use VerifyDeprecations;

    public function testIsRegisteredThroughDirectiveAttribute(): void
    {
        $directive = new ToctreeDirective(new ToctreeBuilder(), new SettingsManager(new ProjectSettings()));

        self::assertSame('toctree', $directive->getName());
        self::assertTrue($directive->isUpgraded());
    }

    public function testCreatesTocNodeFromParserData(): void
    {
        $caption = InlineCompoundNode::getPlainTextInlineNode('Documentation');
        $directive = new Directive('', 'toctree', '', [
            'caption' => new DirectiveOption('caption', 'Documentation'),
            'depth' => new DirectiveOption('depth', '2'),
            'glob' => new DirectiveOption('glob', true),
            'hidden' => new DirectiveOption('hidden', true),
            'reversed' => new DirectiveOption('reversed', true),
            'titlesonly' => new DirectiveOption('titlesonly', true),
            'globExclude' => new DirectiveOption('globExclude', 'index,Index'),
        ]);
        $directive->getOption('caption')->setNode($caption);

        $directiveNode = new DirectiveNode($directive, rawContent: "guide/introduction\n guide/*\n");

        $node = (new ToctreeDirective(new ToctreeBuilder(), new SettingsManager(new ProjectSettings())))
            ->createNode($directiveNode, self::createStub(CompilerContextInterface::class));

        self::assertInstanceOf(TocNode::class, $node);
        self::assertCount(2, $node->getMenuEntries());
        self::assertSame(2, $node->getDepth());
        self::assertTrue($node->isPageLevelOnly());
        self::assertTrue($node->isReversed());
        self::assertSame($caption, $node->getCaption());
        self::assertSame('index,Index', $node->getOption('globExclude'));
        self::assertTrue($node->hasOption('glob'));
        self::assertTrue($node->hasOption('hidden'));
    }

    public function testUsesProjectIndexNameWhenGlobExcludeIsMissing(): void
    {
        $directiveNode = new DirectiveNode(new Directive('', 'toctree', ''), rawContent: '');

        $node = (new ToctreeDirective(new ToctreeBuilder(), new SettingsManager(new ProjectSettings())))
            ->createNode($directiveNode, self::createStub(CompilerContextInterface::class));

        self::assertInstanceOf(TocNode::class, $node);
        self::assertSame('index,Index', $node->getOption('globExclude'));
    }

    #[WithoutErrorHandler]
    public function testLegacyStartingRuleTriggersDeprecation(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/phpDocumentor/guides/issues/1373');

        new ToctreeDirective(new ToctreeBuilder(), self::createStub(Rule::class));
    }

    public function testSettingsManagerCanUseTheLegacyParameterPosition(): void
    {
        $this->expectNoDeprecationWithIdentifier('https://github.com/phpDocumentor/guides/issues/1373');

        new ToctreeDirective(new ToctreeBuilder(), new SettingsManager(new ProjectSettings()));
    }

    #[WithoutErrorHandler]
    public function testLegacyStartingRuleAndThirdSettingsManagerParameterRemainSupported(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/phpDocumentor/guides/issues/1373');

        new ToctreeDirective(
            new ToctreeBuilder(),
            self::createStub(Rule::class),
            new SettingsManager(new ProjectSettings()),
        );
    }
}
