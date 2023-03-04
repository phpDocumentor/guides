<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Intersphinx;

use JsonException;
use RuntimeException;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use function file_get_contents;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class JsonLoader
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /** @return array<mixed> */
    public function loadJsonFromUrl(string $url): array
    {
        $response = $this->client->request(
            'GET',
            $url
        );
        $jsonString = implode("\n", $response->toArray());
        return $this->loadJsonFromString($jsonString, $url);
    }

    /** @return array<mixed> */
    public function loadJsonFromString(string $jsonString, string $url = ''): array
    {
        try {
            $json = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('File loaded from ' . $url . ' did not contain a valid JSON. ', 1671398987, $e);
        }

        if (! is_array($json)) {
            throw new RuntimeException('File loaded from ' . $url . ' did not contain a valid array. ', 1671398988);
        }

        return $json;
    }
}
