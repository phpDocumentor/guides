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
    public function reduceAnchor(string $rawAnchor): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($rawAnchor);

        return strtolower($slug->toString());
    }
}
