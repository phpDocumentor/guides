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

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\ChainedInventoryLinkResolver;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\Inventory;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryGroup;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLink;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLinkResolver;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryRepository;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\ResolvedInventoryLink;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\ReferenceResolvers\NullAnchorNormalizer;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

final class ChainedInventoryLinkResolverTest extends TestCase
{
    private RenderContext&MockObject $renderContext;

    protected function setUp(): void
    {
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->renderContext->method('getLoggerInformation')->willReturn([]);
    }

    public function testResolvesAndCachesRepositoryLookup(): void
    {
        $node = new ReferenceNode('modindex', [], 'some-key');
        $messages = new Messages();
        $link = new InventoryLink('project', '1.0', 'path.html', 'Some title');

        $repository = $this->createMock(InventoryLinkResolver::class);
        assert($repository instanceof InventoryLinkResolver);
        $repository->expects(self::once())->method('hasInventory')->with('some-key')->willReturn(true);
        $repository->expects(self::exactly(2))
            ->method('resolveInventoryLink')
            ->willReturn(new ResolvedInventoryLink('https://example.com/', $link));

        $resolver = new ChainedInventoryLinkResolver([$repository]);

        $resolved = $resolver->resolveInventoryLink($node, $this->renderContext, $messages);
        self::assertNotNull($resolved);
        self::assertEquals('https://example.com/', $resolved->getBaseUrl());

        $resolved = $resolver->resolveInventoryLink($node, $this->renderContext, $messages);
        self::assertNotNull($resolved);
        self::assertEquals('path.html', $resolved->getLink()->getPath());
        self::assertCount(0, $messages->getWarnings());
    }

    public function testAddsWarningWhenNoRepositoryMatchesDomain(): void
    {
        $node = new ReferenceNode('modindex', [], 'missing-domain');
        $messages = new Messages();

        $repository = $this->createMock(InventoryRepository::class);
        assert($repository instanceof InventoryRepository);
        $repository->expects(self::once())->method('hasInventory')->with('missing-domain')->willReturn(false);

        $resolver = new ChainedInventoryLinkResolver([$repository]);
        self::assertNull($resolver->resolveInventoryLink($node, $this->renderContext, $messages));
        self::assertCount(1, $messages->getWarnings());
    }

    public function testFallsBackToLegacyRepositoryInterface(): void
    {
        $node = new ReferenceNode('modindex', [], 'legacy');
        $messages = new Messages();

        $anchorNormalizer = new NullAnchorNormalizer();
        $inventory = new Inventory('https://legacy.example/', $anchorNormalizer);
        $group = new InventoryGroup($anchorNormalizer);
        $group->addLink('modindex', new InventoryLink('project', '1.0', 'legacy.html', 'Legacy'));
        $inventory->addGroup('std:label', $group);

        $repository = new class ($inventory) implements InventoryRepository {
            public function __construct(private readonly Inventory $inventory)
            {
            }

            public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null
            {
                return $this->inventory->getGroup($node, $renderContext, $messages)?->getLink($node, $renderContext, $messages);
            }

            public function hasInventory(string $key): bool
            {
                return $key === 'legacy';
            }

            public function getInventory(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): Inventory|null
            {
                return $this->inventory;
            }
        };

        $resolver = new ChainedInventoryLinkResolver([$repository]);
        $resolved = $resolver->resolveInventoryLink($node, $this->renderContext, $messages);

        self::assertNotNull($resolved);
        self::assertEquals('https://legacy.example/', $resolved->getBaseUrl());
        self::assertEquals('legacy.html', $resolved->getLink()->getPath());
    }
}
