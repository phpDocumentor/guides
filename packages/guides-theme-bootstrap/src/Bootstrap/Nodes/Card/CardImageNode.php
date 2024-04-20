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

namespace phpDocumentor\Guides\Bootstrap\Nodes\Card;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class CardImageNode extends GeneralDirectiveNode
{
    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        protected readonly string $alt = '',
        protected readonly string $position = 'top',
        array $value = [],
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function getPosition(): string
    {
        return $this->position;
    }
}
