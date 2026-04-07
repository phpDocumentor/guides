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

use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\Inventory;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLink;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLinkResolver;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryRepository;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\ResolvedInventoryLink;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InterlinkReferenceResolverTest extends TestCase
{
    private RenderContext&MockObject $renderContext;
    private AnchorNormalizer $anchorNormalizer;

    protected function setUp(): void
    {
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->anchorNormalizer = new NullAnchorNormalizer();
    }

    #[DataProvider('pathProvider')]
    public function testDocumentReducer(string $expected, string $input, string $path): void
    {
        $input = new DocReferenceNode($input, [], 'interlink-target');
        $inventoryLink = new InventoryLink('project', '1.0', $path, '');
        $inventory = new Inventory('base-url/', $this->anchorNormalizer);

        $inventoryRepository = new class ($inventory, $inventoryLink) implements InventoryRepository {
            public function __construct(private readonly Inventory $inventory, private readonly InventoryLink $inventoryLink)
            {
            }

            public function getLink(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): InventoryLink|null
            {
                return $this->inventoryLink;
            }

            public function hasInventory(string $key): bool
            {
                return true;
            }

            public function getInventory(CrossReferenceNode $node, RenderContext $renderContext, Messages $messages): Inventory|null
            {
                return $this->inventory;
            }
        };

        $subject = new InterlinkReferenceResolver($inventoryRepository);
        $messages = new Messages();
        self::assertTrue($subject->resolve($input, $this->renderContext, $messages));
        self::assertEmpty($messages->getWarnings());
        self::assertEquals($expected, $input->getUrl());
    }

    #[DataProvider('pathProvider')]
    public function testDocumentReducerUsesOneCallResolver(string $expected, string $input, string $path): void
    {
        $input = new DocReferenceNode($input, [], 'interlink-target');
        $inventoryLink = new InventoryLink('project', '1.0', $path, '');

        $inventoryRepository = $this->createMock(InventoryLinkResolver::class);
        $inventoryRepository->expects(self::once())
            ->method('resolveInventoryLink')
            ->willReturn(new ResolvedInventoryLink('base-url/', $inventoryLink));

        $subject = new InterlinkReferenceResolver($inventoryRepository);
        $messages = new Messages();

        self::assertTrue($subject->resolve($input, $this->renderContext, $messages));
        self::assertEmpty($messages->getWarnings());
        self::assertEquals($expected, $input->getUrl());
    }

    /** @return string[][] */
    public static function pathProvider(): array
    {
        return [
            'plain' => [
                'expected' => 'base-url/some-document.html',
                'input' => 'some-document',
                'path' => 'some-document.html',
            ],
            'withAnchor' => [
                'expected' => 'base-url/some-document.html#anchor',
                'input' => 'some-document#anchor',
                'path' => 'some-document.html#anchor',
            ],
        ];
    }
}
