<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use Generator;
use phpDocumentor\Guides\Interlink\Exception\InterlinkTargetNotFound;
use phpDocumentor\Guides\ReferenceResolvers\SluggerAnchorNormalizer;
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
        $inventory = $this->inventoryRepository->getInventory('somekey');
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
    public function testInventoryContainsLink(string $expected, string $inventoryKey, string $groupKey, string $linkKey): void
    {
        $link = $this->inventoryRepository->getLink($inventoryKey, $groupKey, $linkKey);
        self::assertEquals($expected, $link->getPath());
    }

    /** @return Generator<string, array{string, string, string, string}> */
    public static function rawAnchorProvider(): Generator
    {
        yield 'Simple label' => [
            'some_page.html#modindex',
            'somekey',
            'std:label',
            'modindex',
        ];

        yield 'Inventory with changed case' => [
            'some_page.html#modindex',
            'SomeKey',
            'std:label',
            'modindex',
        ];

        yield 'Inventory with minus' => [
            'some_page.html#modindex',
            'some-key',
            'std:label',
            'modindex',
        ];

        yield 'Inventory with underscore and changed case' => [
            'some_page.html#modindex',
            'Some_Key',
            'std:label',
            'modindex',
        ];

        yield 'Both with minus' => [
            'some_page.html#php-modindex',
            'somekey',
            'std:label',
            'php-modindex',
        ];

        yield 'Linked with underscore, inventory with minus' => [
            'some_page.html#php-modindex',
            'somekey',
            'std:label',
            'php_modindex',
        ];

        yield 'Linked with underscore, inventory with underscore' => [
            'php-objectsindex.html#php-objectsindex',
            'somekey',
            'std:label',
            'php_objectsindex',
        ];

        yield 'Linked with minus, inventory with underscore' => [
            'php-objectsindex.html#php-objectsindex',
            'somekey',
            'std:label',
            'php-objectsindex',
        ];

        yield 'Doc link' => [
            'Page1/Subpage1.html',
            'somekey',
            'std:doc',
            'Page1/Subpage1',
        ];
    }

    #[DataProvider('notFoundInventoryProvider')]
    public function testInventoryLinkNotFound(string $inventoryKey, string $groupKey, string $linkKey): void
    {
        self::expectException(InterlinkTargetNotFound::class);
        $this->inventoryRepository->getLink($inventoryKey, $groupKey, $linkKey);
    }

    /** @return Generator<string, array{string, string, string}> */
    public static function notFoundInventoryProvider(): Generator
    {
        yield 'Simple labe not found' => [
            'somekey',
            'std:label',
            'non-existant-label',
        ];

        yield 'docs are casesensitve' => [
            'somekey',
            'std:doc',
            'index',
        ];

        yield 'docs are not slugged' => [
            'somekey',
            'std:doc',
            'Page1-Subpage1',
        ];
    }
}
