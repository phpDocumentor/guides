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

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class AnchorHyperlinkResolverTest extends TestCase
{
    private AnchorNormalizer&MockObject $anchorReducer;
    private RenderContext&MockObject $renderContext;
    private Stub&UrlGeneratorInterface $urlGenerator;
    private ProjectNode $projectNode;
    private AnchorHyperlinkResolver $subject;

    protected function setUp(): void
    {
        $this->anchorReducer = $this->createMock(AnchorNormalizer::class);
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->urlGenerator = self::createStub(UrlGeneratorInterface::class);
        $this->projectNode = new ProjectNode('test');
        $this->renderContext->method('getProjectNode')->willReturn($this->projectNode);
        $this->subject = new AnchorHyperlinkResolver(
            $this->anchorReducer,
            $this->urlGenerator,
        );
    }

    public function testFragmentReferenceMatchingSectionReturnsCanonicalUrl(): void
    {
        $internalTarget = new InternalTarget('index', 'section-one', 'Section One', SectionNode::STD_TITLE);
        $this->projectNode->addLinkTarget('section-one', $internalTarget);
        $this->anchorReducer->expects(self::once())->method('reduceAnchor')->with('#section-one')->willReturn('section-one');
        $this->urlGenerator->method('generateCanonicalOutputUrl')->willReturn('/index.html#section-one');

        $node = new HyperLinkNode([new PlainTextInlineNode('Section One')], '#section-one');
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('/index.html#section-one', $node->getUrl());
        self::assertEmpty($messages->getWarnings());
    }

    public function testFragmentReferenceNotMatchingAnySectionFallsBackToBareFragment(): void
    {
        $this->anchorReducer->expects(self::once())->method('reduceAnchor')->with('#nonexistent')->willReturn('nonexistent');

        $node = new HyperLinkNode([new PlainTextInlineNode('Some Link')], '#nonexistent');
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('#nonexistent', $node->getUrl());
        self::assertEmpty($messages->getWarnings());
    }

    public function testBareHashFallsBackToBareFragment(): void
    {
        $this->anchorReducer->expects(self::once())->method('reduceAnchor')->with('#')->willReturn('');

        $node = new HyperLinkNode([new PlainTextInlineNode('Top')], '#');
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('#', $node->getUrl());
        self::assertEmpty($messages->getWarnings());
    }

    public function testNonFragmentReferenceNotMatchingReturnsFalse(): void
    {
        $this->anchorReducer->expects(self::once())->method('reduceAnchor')->with('some-ref')->willReturn('some-ref');

        $node = new HyperLinkNode([new PlainTextInlineNode('Some Link')], 'some-ref');
        $messages = new Messages();
        self::assertFalse($this->subject->resolve($node, $this->renderContext, $messages));
    }
}
