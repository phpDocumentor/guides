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

use function assert;
use function is_scalar;

class NavMenuNode extends MenuNode
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
        return true;
    }

    public static function fromTocNode(TocNode $tocNode, string|null $menuType = null): NavMenuNode
    {
        $node = new NavMenuNode($tocNode->getFiles());
        $node = $node->withMenuEntries($tocNode->getMenuEntries());
        $options = $tocNode->getOptions();
        unset($options['hidden']);
        unset($options['titlesonly']);
        unset($options['maxdepth']);
        if ($menuType !== null) {
            $options['menu'] = $menuType;
        }

        $node = $node->withOptions($options);
        assert($node instanceof NavMenuNode);

        return $node;
    }
}
