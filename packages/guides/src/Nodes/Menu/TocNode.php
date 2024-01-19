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

namespace phpDocumentor\Guides\Nodes\Menu;

use function is_scalar;

/** @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents */
final class TocNode extends MenuNode
{
    public function getDepth(): int
    {
        if ($this->hasOption('depth') && is_scalar($this->getOption('depth'))) {
            return (int) $this->getOption('depth');
        }

        if ($this->hasOption('maxdepth') && is_scalar($this->getOption('maxdepth'))) {
            return (int) $this->getOption('maxdepth');
        }

        return self::DEFAULT_DEPTH;
    }

    public function isPageLevelOnly(): bool
    {
        return $this->hasOption('titlesonly');
    }
}
