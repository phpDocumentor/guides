<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function count;
use function file_get_contents;
use function json_decode;
use function PHPUnit\Framework\assertIsString;

use const JSON_THROW_ON_ERROR;

final class InventoryLoaderTest extends TestCase
{
    private InventoryLoader $inventoryLoader;
    private JsonLoader&MockObject $jsonLoader;
    private InventoryRepository $inventoryRepository;
    /** @var array<string, mixed> */
    private array $json;

    protected function setUp(): void
    {
        $this->jsonLoader = $this->createMock(JsonLoader::class);
        $this->inventoryLoader = new InventoryLoader($this->jsonLoader);
        $this->inventoryRepository = new InventoryRepository($this->inventoryLoader);
        $jsonString = file_get_contents(__DIR__ . '/input/objects.inv.json');
        assertIsString($jsonString);
        $this->json = (array) json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        $inventory = new Inventory('https://example.com/');
        $this->inventoryLoader->loadInventoryFromJson($inventory, $this->json);
        $this->inventoryRepository->addInventory('somekey', $inventory);
    }

    public function testInventoryLoaderLoadsInventory(): void
    {
        $inventory = $this->inventoryRepository->getInventory('somekey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testInventoryIsLoadedExactlyOnce(): void
    {
        $this->jsonLoader->expects(self::once())->method('loadJsonFromUrl')->willReturn($this->json);
        $inventory = new Inventory('https://example.com/');
        $this->inventoryLoader->loadInventory($inventory);
        $this->inventoryLoader->loadInventory($inventory);
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testInventoryLoaderGetInventoryIsCaseInsensitive(): void
    {
        $inventory = $this->inventoryRepository->getInventory('SomeKey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }
}
