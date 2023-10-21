<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use Generator;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AnchorReferenceResolverTest extends TestCase
{
    private AnchorReducer&MockObject $anchorReducer;
    private RenderContext&MockObject $renderContext;
    private ProjectNode&MockObject $projectNode;
    private AnchorReferenceResolver $subject;

    protected function setUp(): void
    {
        $internalTarget = new InternalTarget('some-path', 'some-name');
        $this->projectNode = $this->createMock(ProjectNode::class);
        $this->projectNode->expects(self::once())->method('getInternalTarget')->willReturn($internalTarget);
        $this->anchorReducer = $this->createMock(AnchorReducer::class);
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->renderContext->expects(self::once())->method('getProjectNode')->willReturn($this->projectNode);
        $this->subject = new AnchorReferenceResolver($this->anchorReducer);
    }

    public function testAnchorReducerGetsCalledOndResolvingReference(): void
    {
        $this->anchorReducer->expects(self::once())->method('reduceAnchor')->willReturn('reduced-anchor');
        $input = new ReferenceNode('lorem-ipsum');
        self::assertTrue($this->subject->resolve($input, $this->renderContext));
    }

    public function testResolvedReferenceReturnsCanonicalUrl(): void
    {
        $this->renderContext->expects(self::once())->method('generateCanonicalOutputUrl')->willReturn('canonical-url');
        $input = new ReferenceNode('lorem-ipsum');
        self::assertTrue($this->subject->resolve($input, $this->renderContext));
        self::assertEquals('canonical-url', $input->getUrl());
    }
}
