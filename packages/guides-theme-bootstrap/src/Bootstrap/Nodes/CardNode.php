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

use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardFooterNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardHeaderNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardImageNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class CardNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:card';
    public const LINK_PREFIX = 'card-';

    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        protected readonly TitleNode|null $title = null,
        array $value = [],
        private readonly CardHeaderNode|null $cardHeader = null,
        private readonly CardImageNode|null $cardImage = null,
        private readonly CardFooterNode|null $cardFooter = null,
        private readonly string $id = '',
        private int $cardHeight = 0,
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getTitle(): TitleNode|null
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

    public function getCardHeader(): CardHeaderNode|null
    {
        return $this->cardHeader;
    }

    public function getCardImage(): CardImageNode|null
    {
        return $this->cardImage;
    }

    public function getCardFooter(): CardFooterNode|null
    {
        return $this->cardFooter;
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
        if ($this->getTitle() !== null) {
            return $this->getTitle()->toString();
        }

        if ($this->getCardHeader() !== null) {
            return $this->getCardHeader()->getContent()->toString();
        }

        return 'card ' . $this->id;
    }

    public function isNoindex(): bool
    {
        return $this->id === '';
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }

    public function getCardHeight(): int
    {
        return $this->cardHeight;
    }

    public function setCardHeight(int $cardHeight): CardNode
    {
        $this->cardHeight = $cardHeight;

        return $this;
    }
}
