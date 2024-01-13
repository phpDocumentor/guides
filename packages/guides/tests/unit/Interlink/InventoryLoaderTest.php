<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use Generator;
use phpDocumentor\Guides\Nodes\Inline\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\Messages;
use phpDocumentor\Guides\ReferenceResolvers\SluggerAnchorNormalizer;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

use function count;
use function file_get_contents;
use function json_decode;
use function PHPUnit\Framework\assertIsString;

use const JSON_THROW_ON_ERROR;

final class InventoryLoaderTest extends TestCase
{
    private DefaultInventoryLoader $inventoryLoader;
    private JsonLoader&MockObject $jsonLoader;
    private InventoryRepository $inventoryRepository;
    private RenderContext&MockObject $renderContext;
    /** @var array<string, mixed> */
    private array $json;

    protected function setUp(): void
    {
        $this->jsonLoader = $this->createMock(JsonLoader::class);
        $this->inventoryLoader = new DefaultInventoryLoader(
            self::createStub(NullLogger::class),
            $this->jsonLoader,
            new SluggerAnchorNormalizer(),
        );
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->inventoryRepository = new DefaultInventoryRepository(new SluggerAnchorNormalizer(), $this->inventoryLoader, []);
        $jsonString = file_get_contents(__DIR__ . '/fixtures/objects.inv.json');
        assertIsString($jsonString);
        $this->json = (array) json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        $inventory = new Inventory('https://example.com/', new SluggerAnchorNormalizer());
        $this->inventoryLoader->loadInventoryFromJson($inventory, $this->json);
        $this->inventoryRepository->addInventory('somekey', $inventory);
        $this->inventoryRepository->addInventory('some-key', $inventory);
    }

    public function testInventoryLoaderLoadsInventory(): void
    {
        $node = new DocReferenceNode('SomeDocument', '', 'somekey');
        $inventory = $this->inventoryRepository->getInventory($node, $this->renderContext, new Messages());
        self::assertTrue($inventory instanceof Inventory);
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testInventoryIsLoadedExactlyOnce(): void
    {
        $this->jsonLoader->expects(self::once())->method('loadJsonFromUrl')->willReturn($this->json);
        $inventory = new Inventory('https://example.com/', new SluggerAnchorNormalizer());
        $this->inventoryLoader->loadInventory($inventory);
        $this->inventoryLoader->loadInventory($inventory);
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    #[DataProvider('rawAnchorProvider')]
    public function testInventoryContainsLink(string $expected, CrossReferenceNode $node): void
    {
        $link = $this->inventoryRepository->getLink($node, $this->renderContext, new Messages());
        self::assertTrue($link instanceof InventoryLink);
        self::assertEquals($expected, $link->getPath());
    }

    /** @return Generator<string, array{string, CrossReferenceNode}> */
    public static function rawAnchorProvider(): Generator
    {
        yield 'Simple label' => [
            'some_page.html#modindex',
            new ReferenceNode('modindex', '', 'somekey'),
        ];

        yield 'Inventory with changed case' => [
            'some_page.html#modindex',
            new ReferenceNode('modindex', '', 'SomeKey'),
        ];

        yield 'Inventory with minus' => [
            'some_page.html#modindex',
            new ReferenceNode('modindex', '', 'some-key'),
        ];

        yield 'Inventory with underscore and changed case' => [
            'some_page.html#modindex',
            new ReferenceNode('modindex', '', 'Some_Key'),
        ];

        yield 'Both with minus' => [
            'some_page.html#php-modindex',
            new ReferenceNode('php-modindex', '', 'somekey'),
        ];

        yield 'Linked with underscore, inventory with minus' => [
            'some_page.html#php-modindex',
            new ReferenceNode('php_modindex', '', 'somekey'),
        ];

        yield 'Linked with underscore, inventory with underscore' => [
            'php-objectsindex.html#php-objectsindex',
            new ReferenceNode('php_objectsindex', '', 'somekey'),
        ];

        yield 'Linked with minus, inventory with underscore' => [
            'php-objectsindex.html#php-objectsindex',
            new ReferenceNode('php-objectsindex', '', 'somekey'),
        ];

        yield 'Doc link' => [
            'Page1/Subpage1.html',
            new DocReferenceNode('Page1/Subpage1', '', 'somekey'),
        ];
    }

    #[DataProvider('notFoundInventoryProvider')]
    public function testInventoryLinkNotFound(CrossReferenceNode $node): void
    {
        $messages = new Messages();
        $this->inventoryRepository->getLink($node, $this->renderContext, $messages);
        self::assertCount(1, $messages->getWarnings());
    }

    /** @return Generator<string, array{CrossReferenceNode}> */
    public static function notFoundInventoryProvider(): Generator
    {
        yield 'Simple labe not found' => [
            new ReferenceNode('non-existant-label', '', 'somekey'),
        ];

        yield 'docs are casesensitve' => [
            new DocReferenceNode('index', '', 'somekey'),
        ];

        yield 'docs are not slugged' => [
            new DocReferenceNode('Page1-Subpage1', '', 'somekey'),
        ];
    }
}
