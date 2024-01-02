<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\References;

use function preg_match;

trait EmbeddedReferenceParser
{
    private function extractEmbeddedReference(string $text): ReferenceData
    {
        preg_match('/^(.*?)(?:(?:\s|^)<([^<]+)>)?$/s', $text, $matches);

        $text = $matches[1] === '' ? null : $matches[1];
        $reference = $matches[1];

        if (isset($matches[2])) {
            // there is an embedded URI, text and URI are different
            $reference = $matches[2];
        } else {
            $text = null;
        }

        return new ReferenceData($reference, $text);
    }
}
