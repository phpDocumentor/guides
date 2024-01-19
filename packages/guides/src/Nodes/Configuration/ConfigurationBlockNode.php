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

namespace phpDocumentor\Guides\Nodes\Configuration;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<list<ConfigurationTab>> */
final class ConfigurationBlockNode extends AbstractNode
{
    /** @param list<ConfigurationTab> $tabs */
    public function __construct(
        array $tabs,
    ) {
        $this->value = $tabs;
    }

    /** @return list<ConfigurationTab> */
    public function getTabs(): array
    {
        return $this->value;
    }
}
