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

namespace phpDocumentor\Guides\ReferenceResolvers\Interlink;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class JsonLoaderTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private CacheInterface&MockObject $cache;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
    }

    public function testLoadJsonFromUrlWithDefaultCache(): void
    {
        $url = 'https://example.com/inventory.json';
        $expectedData = ['key' => 'value', 'items' => []];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($expectedData);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn($response);

        // Uses default ArrayAdapter when no cache provided
        $loader = new JsonLoader($this->httpClient);
        $result = $loader->loadJsonFromUrl($url);

        self::assertSame($expectedData, $result);
    }

    public function testLoadJsonFromUrlWithCacheMiss(): void
    {
        $url = 'https://example.com/inventory.json';
        $expectedData = ['key' => 'value', 'items' => []];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($expectedData);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn($response);

        // Cache miss - returns null
        $this->cache->expects(self::once())
            ->method('get')
            ->willReturn(null);

        // Should store in cache after fetch
        $this->cache->expects(self::once())
            ->method('set')
            ->with(
                self::stringStartsWith('guides_inventory_'),
                $expectedData,
            )
            ->willReturn(true);

        $loader = new JsonLoader($this->httpClient, $this->cache);
        $result = $loader->loadJsonFromUrl($url);

        self::assertSame($expectedData, $result);
    }

    public function testLoadJsonFromUrlWithCacheHit(): void
    {
        $url = 'https://example.com/inventory.json';
        $cachedData = ['key' => 'cached_value'];

        // Should NOT fetch from network
        $this->httpClient->expects(self::never())->method('request');

        // Cache hit - returns cached data
        $this->cache->expects(self::once())
            ->method('get')
            ->willReturn($cachedData);

        // Should NOT write to cache
        $this->cache->expects(self::never())->method('set');

        $loader = new JsonLoader($this->httpClient, $this->cache);
        $result = $loader->loadJsonFromUrl($url);

        self::assertSame($cachedData, $result);
    }

    public function testLoadJsonFromUrlCacheDeduplicatesRequests(): void
    {
        $url = 'https://example.com/inventory.json';
        $expectedData = ['key' => 'value'];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($expectedData);

        // HTTP client should only be called once - cache handles deduplication
        $this->httpClient->expects(self::once())
            ->method('request')
            ->willReturn($response);

        // Default ArrayAdapter provides in-memory caching
        $loader = new JsonLoader($this->httpClient);

        // Multiple calls with same URL - second and third use cache
        $result1 = $loader->loadJsonFromUrl($url);
        $result2 = $loader->loadJsonFromUrl($url);
        $result3 = $loader->loadJsonFromUrl($url);

        self::assertSame($expectedData, $result1);
        self::assertSame($expectedData, $result2);
        self::assertSame($expectedData, $result3);
    }

    public function testLoadJsonFromUrlCacheKeyIsDeterministic(): void
    {
        $url = 'https://example.com/inventory.json';
        $expectedData = ['key' => 'value'];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($expectedData);

        $this->httpClient->method('request')->willReturn($response);

        $capturedKey = null;
        $this->cache->method('get')->willReturn(null);
        $this->cache->method('set')
            ->willReturnCallback(static function (string $key) use (&$capturedKey): bool {
                $capturedKey = $key;

                return true;
            });

        $loader1 = new JsonLoader($this->httpClient, $this->cache);
        $loader1->loadJsonFromUrl($url);

        $firstKey = $capturedKey;

        // Create new loader, same URL should produce same cache key
        $loader2 = new JsonLoader($this->httpClient, $this->cache);
        $loader2->loadJsonFromUrl($url);

        self::assertSame($firstKey, $capturedKey);
        self::assertNotNull($capturedKey);
        self::assertStringStartsWith('guides_inventory_', $capturedKey);
    }

    public function testLoadJsonFromStringDoesNotUseCache(): void
    {
        $jsonString = '{"key": "value"}';

        // Cache should never be accessed for string loading
        $this->cache->expects(self::never())->method('get');
        $this->cache->expects(self::never())->method('set');

        $loader = new JsonLoader($this->httpClient, $this->cache);
        $result = $loader->loadJsonFromString($jsonString, 'test-url');

        self::assertSame(['key' => 'value'], $result);
    }

    public function testLoadJsonFromStringThrowsOnInvalidJson(): void
    {
        $loader = new JsonLoader($this->httpClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('did not contain valid JSON');

        $loader->loadJsonFromString('not valid json', 'test-url');
    }

    public function testLoadJsonFromStringThrowsOnNonArrayJson(): void
    {
        $loader = new JsonLoader($this->httpClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('did not contain a valid array');

        $loader->loadJsonFromString('"just a string"', 'test-url');
    }

    public function testLoadJsonFromStringWithEmptyUrlHasClearMessage(): void
    {
        $loader = new JsonLoader($this->httpClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('JSON content loaded did not contain valid JSON');

        $loader->loadJsonFromString('invalid', '');
    }
}
