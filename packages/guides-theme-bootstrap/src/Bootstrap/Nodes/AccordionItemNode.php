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

namespace phpDocumentor\Guides\Bootstrap\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class AccordionItemNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:accordion';
    public const LINK_PREFIX = 'accordion-';

    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        protected readonly TitleNode $title,
        array $value = [],
        private readonly string $id = '',
        private readonly bool $show = false,
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlainContent(): string
    {
        return $this->plainContent;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
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
        return $this->getTitle()->toString();
    }

    public function isNoindex(): bool
    {
        return $this->id === '';
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }

    public function isShow(): bool
    {
        return $this->show;
    }
}
