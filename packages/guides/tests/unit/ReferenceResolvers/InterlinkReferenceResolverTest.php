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

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\Inventory;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLink;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryRepository;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InterlinkReferenceResolverTest extends TestCase
{
    private RenderContext&MockObject $renderContext;
    private MockObject&InventoryRepository $inventoryRepository;
    private InterlinkReferenceResolver $subject;
    private AnchorNormalizer $anchorNormalizer;

    protected function setUp(): void
    {
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->inventoryRepository = $this->createMock(InventoryRepository::class);
        $this->anchorNormalizer = new NullAnchorNormalizer();
        $this->subject = new InterlinkReferenceResolver($this->inventoryRepository);
    }

    #[DataProvider('pathProvider')]
    public function testDocumentReducer(string $expected, string $input, string $path): void
    {
        $input = new DocReferenceNode($input, '', 'interlink-target');
        $inventoryLink = new InventoryLink('project', '1.0', $path, '');
        $inventory = new Inventory('base-url/', $this->anchorNormalizer);
        $this->inventoryRepository->expects(self::once())->method('getInventory')->willReturn($inventory);
        $this->inventoryRepository->expects(self::once())->method('getLink')->willReturn($inventoryLink);
        $messages = new Messages();
        self::assertTrue($this->subject->resolve($input, $this->renderContext, $messages));
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
