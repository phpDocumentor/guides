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

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryGroup;
use phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLink;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\ReferenceResolvers\NullAnchorNormalizer;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InventoryGroupTest extends TestCase
{
    private InventoryGroup $inventoryGroup;

    private RenderContext&MockObject $renderContext;

    protected function setUp(): void
    {
        $this->inventoryGroup = new InventoryGroup(new NullAnchorNormalizer());
        $this->renderContext = $this->createMock(RenderContext::class);
    }

    #[DataProvider('linkProvider')]
    public function testGetLinkFromInterlinkGroup(string $expected, string $input, string $path): void
    {
        $this->inventoryGroup->addLink($path, new InventoryLink('', '', $path . '.html', ''));
        $messages = new Messages();
        $link = $this->inventoryGroup->getLink(
            new DocReferenceNode($input, '', 'interlink'),
            $this->renderContext,
            $messages,
        );
        self::assertEmpty($messages->getWarnings());
        self::assertEquals($expected, $link?->getPath());
    }

    /** @return string[][] */
    public static function linkProvider(): array
    {
        return [
            'plain' => [
                'expected' => 'some-document.html',
                'input' => 'some-document',
                'path' => 'some-document',
            ],
            'withAnchor' => [
                'expected' => 'some-document.html#anchor',
                'input' => 'some-document#anchor',
                'path' => 'some-document',
            ],
        ];
    }
}
