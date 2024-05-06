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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class AccordionNode extends GeneralDirectiveNode
{
    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        protected readonly string $id = 'accordion',
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAnchor(): string
    {
        return 'accordion-parent-' . $this->id;
    }
}
