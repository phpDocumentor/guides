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

namespace phpDocumentor\Guides\ReferenceResolvers;

use Symfony\Component\String\Slugger\AsciiSlugger;

use function strtolower;

final class SluggerAnchorNormalizer implements AnchorNormalizer
{
    private AsciiSlugger|null $slugger = null;

    /** @var array<string, string> */
    private array $cache = [];

    public function reduceAnchor(string $rawAnchor): string
    {
        // Check cache first - same anchors are resolved many times
        if (isset($this->cache[$rawAnchor])) {
            return $this->cache[$rawAnchor];
        }

        if ($this->slugger === null) {
            $this->slugger = new AsciiSlugger();
        }

        $slug = $this->slugger->slug($rawAnchor);
        $result = strtolower($slug->toString());

        // Cache the result for future calls
        $this->cache[$rawAnchor] = $result;

        return $result;
    }
}
