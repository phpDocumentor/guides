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
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\MultipleLinkTargetsNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Describes a command line argument or switch. Option argument names should be enclosed in angle brackets.
 *
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/domains.html#directive-option
 *
 * @extends CompoundNode<Node>
 */
final class OptionNode extends CompoundNode implements LinkTargetNode, MultipleLinkTargetsNode
{
    public const LINK_TYPE = 'std:option';

    /**
     * @param list<Node> $value
     * @param string[]  $additionalIds
     */
    public function __construct(
        private readonly string $id,
        private readonly string $plainContent,
        private readonly array $additionalIds,
        array $value = [],
    ) {
        parent::__construct($value);
    }

    public function getPlainContent(): string
    {
        return $this->plainContent;
    }

    public function getLinkType(): string
    {
        return self::LINK_TYPE;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLinkText(): string
    {
        return $this->plainContent;
    }

    /** @return string[] */
    public function getAdditionalIds(): array
    {
        return $this->additionalIds;
    }
}
