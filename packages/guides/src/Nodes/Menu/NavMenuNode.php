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

final class NavMenuNode extends MenuNode
{
    private string|null $currentPath = null;
    /** @var string[] */
    private array $rootlinePaths = [];

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
        return true;
    }

    public function withCurrentPath(string|null $currentPath): NavMenuNode
    {
        $that = clone $this;
        $that->currentPath = $currentPath;

        return $that;
    }

    /** @param string[] $rootlinePaths */
    public function withRootlinePaths(array $rootlinePaths): NavMenuNode
    {
        $that = clone $this;
        $that->rootlinePaths = $rootlinePaths;

        return $that;
    }

    public function getCurrentPath(): string|null
    {
        return $this->currentPath;
    }

    /** @return string[] */
    public function getRootlinePaths(): array
    {
        return $this->rootlinePaths;
    }
}
