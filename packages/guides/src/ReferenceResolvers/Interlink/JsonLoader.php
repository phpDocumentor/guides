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

use JsonException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function hash;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Loads JSON data from URLs with PSR-16 caching support.
 *
 * By default, uses an in-memory ArrayAdapter for request deduplication.
 * For persistent caching across requests, inject a FilesystemAdapter or RedisAdapter.
 * For multi-tier caching (memory + disk), use Symfony's ChainAdapter.
 */
class JsonLoader
{
    private const CACHE_KEY_PREFIX = 'guides_inventory_';

    private readonly CacheInterface $cache;

    /** @param CacheInterface|null $cache PSR-16 cache implementation, or null for in-memory only */
    public function __construct(
        private readonly HttpClientInterface $client,
        CacheInterface|null $cache = null,
    ) {
        $this->cache = $cache ?? new Psr16Cache(new ArrayAdapter());
    }

    /** @return array<mixed> */
    public function loadJsonFromUrl(string $url): array
    {
        $cacheKey = $this->getCacheKey($url);
        $cached = $this->cache->get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        // Fetch from network
        $response = $this->client->request('GET', $url);
        $data = $response->toArray();

        // Store in cache (uses adapter's configured TTL)
        $this->cache->set($cacheKey, $data);

        return $data;
    }

    /** @return array<mixed> */
    public function loadJsonFromString(string $jsonString, string $url = ''): array
    {
        $source = $url !== '' ? ' from ' . $url : '';

        try {
            $json = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('JSON content loaded' . $source . ' did not contain valid JSON.', 1_671_398_987, $e);
        }

        if (!is_array($json)) {
            throw new RuntimeException('JSON content loaded' . $source . ' did not contain a valid array.', 1_671_398_988);
        }

        return $json;
    }

    /**
     * Generate a cache key for the given URL.
     *
     * Uses xxh128 hash to create a short, unique, filesystem-safe key.
     */
    private function getCacheKey(string $url): string
    {
        return self::CACHE_KEY_PREFIX . hash('xxh128', $url);
    }
}
