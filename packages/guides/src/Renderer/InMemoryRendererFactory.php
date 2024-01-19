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

namespace phpDocumentor\Guides\Renderer;

use Exception;

use function sprintf;

final class InMemoryRendererFactory implements TypeRendererFactory
{
    /** @param iterable<TypeRenderer> $renderSets */
    public function __construct(private readonly iterable $renderSets)
    {
    }

    public function getRenderSet(string $outputFormat): TypeRenderer
    {
        foreach ($this->renderSets as $format => $renderSet) {
            if ($format === $outputFormat) {
                return $renderSet;
            }
        }

        throw new Exception(sprintf('No render set found for output format "%s"', $outputFormat));
    }
}
