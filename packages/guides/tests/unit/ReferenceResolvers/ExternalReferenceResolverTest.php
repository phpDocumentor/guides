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

use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExternalReferenceResolverTest extends TestCase
{
    private RenderContext&MockObject $renderContext;
    private ExternalReferenceResolver $subject;

    protected function setUp(): void
    {
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->subject = new ExternalReferenceResolver();
    }

    public function testFragmentOnlyReferenceIsNotResolved(): void
    {
        $node = new HyperLinkNode([new PlainTextInlineNode('#section-one')], '#section-one');
        $messages = new Messages();
        self::assertFalse($this->subject->resolve($node, $this->renderContext, $messages));
    }

    public function testHttpUrlIsResolved(): void
    {
        $node = new HyperLinkNode([new PlainTextInlineNode('Example')], 'https://example.com');
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('https://example.com', $node->getUrl());
    }

    public function testEmailIsResolved(): void
    {
        $node = new HyperLinkNode([new PlainTextInlineNode('Email')], 'user@example.com');
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($node, $this->renderContext, $messages));
        self::assertEquals('mailto:user@example.com', $node->getUrl());
    }

    public function testUnknownSchemeIsNotResolved(): void
    {
        $node = new HyperLinkNode([new PlainTextInlineNode('Link')], 'some-page');
        $messages = new Messages();
        self::assertFalse($this->subject->resolve($node, $this->renderContext, $messages));
    }
}
