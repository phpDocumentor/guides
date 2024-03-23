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
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;

/**
 * The confval directive configuration values.
 *
 * https://sphinx-toolbox.readthedocs.io/en/stable/extensions/confval.html
 *
 * @extends CompoundNode<Node>
 */
final class ConfvalNode extends CompoundNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:confval';
    public const LINK_PREFIX = 'confval-';

    /**
     * @param list<Node> $value
     * @param array<string, InlineCompoundNode>  $additionalOptions
     */
    public function __construct(
        private readonly string $id,
        private readonly string $plainContent,
        private readonly InlineCompoundNode|null $type = null,
        private readonly bool $required = false,
        private readonly InlineCompoundNode|null $default = null,
        private readonly array $additionalOptions = [],
        array $value = [],
        private readonly bool $noindex = false,
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

    public function getAnchor(): string
    {
        return self::LINK_PREFIX . $this->id;
    }

    public function getLinkText(): string
    {
        return $this->plainContent;
    }

    public function getType(): InlineCompoundNode|null
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefault(): InlineCompoundNode|null
    {
        return $this->default;
    }

    /** @return array<string,InlineCompoundNode> */
    public function getAdditionalOptions(): array
    {
        return $this->additionalOptions;
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }

    public function isNoindex(): bool
    {
        return $this->noindex;
    }
}
