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

namespace phpDocumentor\Guides\Nodes;

use function implode;

final class MathNode extends TextNode
{
    /** @param string[] $lines */
    public function __construct(array $lines)
    {
        parent::__construct(implode("\n", $lines));
    }
}
