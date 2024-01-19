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
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class JsonLoader
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /** @return array<mixed> */
    public function loadJsonFromUrl(string $url): array
    {
        $response = $this->client->request(
            'GET',
            $url,
        );

        return $response->toArray();
    }

    /** @return array<mixed> */
    public function loadJsonFromString(string $jsonString, string $url = ''): array
    {
        try {
            $json = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('File loaded from ' . $url . ' did not contain a valid JSON. ', 1_671_398_987, $e);
        }

        if (!is_array($json)) {
            throw new RuntimeException('File loaded from ' . $url . ' did not contain a valid array. ', 1_671_398_988);
        }

        return $json;
    }
}
