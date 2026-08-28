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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode;
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

    public function testSupportsContentTypeItemNode(): void
    {
        self::assertTrue($this->renderer->supports(ContentTypeItemNode::class));
    }

    public function testSupportsContentTypeOverviewNode(): void
    {
        self::assertTrue($this->renderer->supports(ContentTypeOverviewNode::class));
    }

    public function testDoesNotSupportArbitraryNodes(): void
    {
        self::assertFalse($this->renderer->supports(TitleNode::class));
    }

    public function testRenderPageNodeUsesPageTemplate(): void
    {
        $pageNode      = new PageNode('about', []);
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($pageNode, $renderContext);

        self::assertSame('structure/page.html.twig', $this->templateRenderer->getTemplate());
    }

    public function testRenderContentTypeItemNodeUsesItemTemplate(): void
    {
        $doc = new DocumentNode('news/item', 'news/item');
        $item = ContentTypeItemNode::from($doc)->withItemTemplate('structure/content-type-item.html.twig');
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($item, $renderContext);

        self::assertSame('structure/content-type-item.html.twig', $this->templateRenderer->getTemplate());
    }

    public function testRenderContentTypeItemNodeFallsBackToDefaultTemplate(): void
    {
        $doc  = new DocumentNode('news/item', 'news/item');
        $item = ContentTypeItemNode::from($doc); // no template set
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($item, $renderContext);

        self::assertSame('structure/content-type-item.html.twig', $this->templateRenderer->getTemplate());
    }

    public function testRenderContentTypeOverviewNodeUsesOverviewTemplate(): void
    {
        $overview      = new ContentTypeOverviewNode('news/index', 'News', 'structure/content-type-overview.html.twig');
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($overview, $renderContext);

        self::assertSame('structure/content-type-overview.html.twig', $this->templateRenderer->getTemplate());
    }

    public function testRenderContentTypeOverviewNodePassesItemsToTemplate(): void
    {
        $item1    = ContentTypeItemNode::from(new DocumentNode('news/a', 'news/a'));
        $overview = new ContentTypeOverviewNode(
            'news/index',
            'News',
            'structure/content-type-overview.html.twig',
            [$item1],
        );
        $renderContext = $this->createStub(RenderContext::class);

        $this->renderer->render($overview, $renderContext);

        $context = $this->templateRenderer->getContext();
        self::assertArrayHasKey('items', $context);
        self::assertSame([$item1], $context['items']);
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
