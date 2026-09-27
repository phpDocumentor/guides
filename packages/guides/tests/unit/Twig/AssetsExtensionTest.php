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

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\DataNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AssetsExtension::class)]
final class AssetsExtensionTest extends TestCase
{
    public function testRendersNothingForANodeWithoutData(): void
    {
        self::assertSame('', $this->extension()->renderDataAttributes($this->section()));
    }

    public function testRendersEachPieceOfDataAsAnAttribute(): void
    {
        $section = $this->section();
        $section->addMetaData(new DataNode('guides-index-terms', ['installation', 'configuration']));
        $section->addMetaData(new DataNode('pagefind-weight', ['2']));

        self::assertSame(
            ' data-guides-index-terms="installation,configuration" data-pagefind-weight="2"',
            $this->extension()->renderDataAttributes($section),
        );
    }

    public function testEscapesWhatAnAuthorWrote(): void
    {
        $section = $this->section();
        $section->addMetaData(new DataNode('guides-index-terms', ['"quoted" & <tagged>']));

        self::assertSame(
            ' data-guides-index-terms="&quot;quoted&quot; &amp; &lt;tagged&gt;"',
            $this->extension()->renderDataAttributes($section),
        );
    }

    private function extension(): AssetsExtension
    {
        return new AssetsExtension(
            $this->createStub(NodeRenderer::class),
            $this->createStub(UrlGeneratorInterface::class),
        );
    }

    private function section(): SectionNode
    {
        return new SectionNode(new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Installation')]), 1, 'installation'));
    }
}
