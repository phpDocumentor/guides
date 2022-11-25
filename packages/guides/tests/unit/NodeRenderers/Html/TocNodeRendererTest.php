<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Meta\Entry;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\SpyRenderer;
use phpDocumentor\Guides\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TocNodeRendererTest extends TestCase
{
    use ProphecyTrait;

    public function testRenderBuildsTreeFromFiles(): void
    {
        $tocNode = new TocNode(
            [
                '/index',
                '/other'
            ]
        );

        $metas = new Metas(
            [
                'index' => new Entry(
                    '/index',
                    '/index',
                    new TitleNode(new SpanNode('Title 1'), 1),
                    [
                        new TitleNode(new SpanNode('Title 2'), 2),
                        new TitleNode(new SpanNode('Title 3'), 3),
                    ],
                    [],
                    [],
                    0
                )
            ]
        );


        $renderer = new SpyRenderer();
        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);

        $nodeRenderer = new TocNodeRenderer(
            $renderer,
            $urlGenerator->reveal(),
            $metas
        );

        $renderContext = RenderContext::forDocument(
            new DocumentNode('test', '/index'),
            $this->prophesize(FilesystemInterface::class)->reveal(),
            $this->prophesize(FilesystemInterface::class)->reveal(),
            '/',
            $metas,
            $urlGenerator->reveal(),
            'html'
        );

        $nodeRenderer->render(
            $tocNode,
            $renderContext
        );

        self::assertEquals(
            [
                'tocNode' => $tocNode,
                'tocItems' => [
                    [
                        'targetId' => 'title-1',
                        'targetUrl' => '/index.html',
                        'title' => 'Title 1',
                        'level' => 1,
                        'children' => [
                            [
                                'targetId' => 'title-2',
                                'targetUrl' => '/index.html#title-2',
                                'title' => 'Title 2',
                                'level' => 2,
                                'children' => [
                                    []
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $renderer->getContext()
        );
    }
}
