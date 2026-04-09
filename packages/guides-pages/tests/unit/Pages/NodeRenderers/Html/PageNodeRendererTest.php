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

namespace phpDocumentor\Guides\Pages\NodeRenderers\Html;

use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Pages\Nodes\PageNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\SpyTemplateRenderer;
use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\NodeRenderers\Html\PageNodeRenderer */
final class PageNodeRendererTest extends TestCase
{
    private SpyTemplateRenderer $templateRenderer;
    private PageNodeRenderer $renderer;

    protected function setUp(): void
    {
        $this->templateRenderer = new SpyTemplateRenderer();
        $this->renderer         = new PageNodeRenderer($this->templateRenderer);
    }

    public function testSupportsPageNode(): void
    {
        self::assertTrue($this->renderer->supports(PageNode::class));
    }

    public function testDoesNotSupportArbitraryNodes(): void
    {
        self::assertFalse($this->renderer->supports(TitleNode::class));
    }

    public function testRenderDelegatesToCorrectTemplate(): void
    {
        $pageNode      = new PageNode('about', []);
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($pageNode, $renderContext);

        self::assertSame('structure/page.html.twig', $this->templateRenderer->getTemplate());
    }

    public function testRenderPassesNodeAndTitleToTemplate(): void
    {
        $pageNode      = new PageNode('about', []);
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($pageNode, $renderContext);

        $context = $this->templateRenderer->getContext();
        self::assertArrayHasKey('node', $context);
        self::assertArrayHasKey('title', $context);
        self::assertSame($pageNode, $context['node']);
        self::assertNull($context['title']);
    }

    public function testRenderReturnsTemplateOutput(): void
    {
        $pageNode      = new PageNode('about', []);
        $renderContext = $this->createStub(RenderContext::class);

        $output = $this->renderer->render($pageNode, $renderContext);

        self::assertSame('spy', $output);
    }
}
