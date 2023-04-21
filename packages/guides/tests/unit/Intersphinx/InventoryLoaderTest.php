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
        $this->inventoryRepository = new InventoryRepository([]);
        $this->inventoryLoader = new InventoryLoader($this->inventoryRepository, $this->jsonLoader);
        $jsonString = file_get_contents(__DIR__ . '/input/objects.inv.json');
        assertIsString($jsonString);
        $this->json = (array) json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        $this->inventoryLoader->loadInventoryFromJson('somekey', 'https://example.com/', $this->json);
    }

    public function testInventoryLoaderLoadsInventory(): void
    {
        $inventory = $this->inventoryLoader->getInventoryRepository()->getInventory('somekey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testLoadInventoryFromUrl(): void
    {
        $this->jsonLoader->expects(self::atLeastOnce())->method('loadJsonFromUrl')->willReturn($this->json);
        $this->inventoryLoader->loadInventoryFromUrl('somekey', 'https://example.com/');
        $inventory = $this->inventoryLoader->getInventoryRepository()->getInventory('somekey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testInventoryLoaderGetInventoryIsCaseInsensitive(): void
    {
        $inventory = $this->inventoryLoader->getInventoryRepository()->getInventory('SomeKey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }

    public function testInventoryKeyIsCaseInsensitive(): void
    {
        $inventoryLoaderWithCamelCaseKey = new InventoryLoader($this->inventoryRepository, $this->jsonLoader);
        $inventoryLoaderWithCamelCaseKey->loadInventoryFromJson('CamelCaseKey', 'https://example.com/', $this->json);
        $inventory = $inventoryLoaderWithCamelCaseKey->getInventoryRepository()->getInventory('camelcasekey');
        self::assertGreaterThan(1, count($inventory->getGroups()));
    }
}
