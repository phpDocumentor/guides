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

namespace phpDocumentor\Guides\NodeRenderers\PreRenderers;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\TestCase;

final class PreNodeRendererFactoryTest extends TestCase
{
    public function testItAsksAPreRendererAgainForEveryNodeWhenSupportsIsNotCachable(): void
    {
        // supports() looks at the node's value, so two nodes of the same class can differ. Deciding once
        // per class would freeze whatever the first node happened to yield for all the others.
        $preRenderer = new class implements PreNodeRenderer {
            public function supports(Node $node): bool
            {
                return $node->getValue() === 'pre-render me';
            }

            public function execute(Node $node, RenderContext $renderContext): Node
            {
                return $node;
            }
        };

        $factory = new PreNodeRendererFactory($this->innerFactory(), [$preRenderer]);

        self::assertNotInstanceOf(PreRenderer::class, $factory->get(new RawNode('leave me alone')));
        self::assertInstanceOf(PreRenderer::class, $factory->get(new RawNode('pre-render me')));
    }

    public function testItDecidesOncePerClassWhenEveryPreRendererSaysSupportsIsCachable(): void
    {
        $preRenderer = new class implements PreNodeRenderer, PreNodeRendererCachableSupports {
            public int $calls = 0;

            public function cacheSupport(): bool
            {
                return true;
            }

            public function supports(Node $node): bool
            {
                $this->calls++;

                return true;
            }

            public function execute(Node $node, RenderContext $renderContext): Node
            {
                return $node;
            }
        };

        $factory = new PreNodeRendererFactory($this->innerFactory(), [$preRenderer]);

        $first = $factory->get(new RawNode('one'));
        $second = $factory->get(new RawNode('two'));

        self::assertSame($first, $second);
        self::assertSame(1, $preRenderer->calls, 'supports() is asked once for the class, not once per node');
    }

    private function innerFactory(): NodeRendererFactory
    {
        $renderer = self::createStub(NodeRenderer::class);

        $factory = self::createStub(NodeRendererFactory::class);
        $factory->method('get')->willReturn($renderer);

        return $factory;
    }
}
