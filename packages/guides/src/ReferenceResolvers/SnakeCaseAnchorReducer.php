<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use function preg_replace;
use function strtolower;
use function trim;

class SnakeCaseAnchorReducer implements AnchorReducer
{
    public function reduceAnchor(string $rawAnchor): string
    {
        $cleanedAnchor = strtolower($rawAnchor);

        // Remove unwanted characters and convert to lowercase
        $cleanedAnchor = preg_replace('/[^a-z0-9]+/i', '-', $cleanedAnchor);

        // Remove leading and trailing underscores
        $cleanedAnchor = trim($cleanedAnchor ?? '', '-');

        // Remove consecutive underscores
        $cleanedAnchor = preg_replace('/-+/', '-', $cleanedAnchor);

        return $cleanedAnchor ?? '';
    }
}
