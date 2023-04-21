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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

use function sprintf;

/** @extends CompoundNode<Node> */
class VersionChangeNode extends CompoundNode
{
    private string $versionLabel;

    /** {@inheritDoc} */
    public function __construct(private string $type, string $versionLabel, private string $versionModified, array $value)
    {
        parent::__construct($value);

        $this->versionLabel = sprintf($versionLabel, $versionModified);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getVersionLabel(): string
    {
        return $this->versionLabel;
    }

    public function getVersionModified(): string
    {
        return $this->versionModified;
    }
}
