<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function preg_match;

trait EmbeddedUriParser
{
    /** @return array{text:?string,uri:string} */
    private function extractEmbeddedUri(string $text): array
    {
        preg_match('/^(.*?)(?:(?:\s|^)<([^<]+)>)?$/s', $text, $matches);

        $text = $matches[1] === '' ? null : $matches[1];
        $uri = $matches[1];

        if (isset($matches[2])) {
            // there is an embedded URI, text and URI are different
            $uri = $matches[2];
        } else {
            $text = null;
        }

        return ['text' => $text, 'uri' => $uri];
    }
}
