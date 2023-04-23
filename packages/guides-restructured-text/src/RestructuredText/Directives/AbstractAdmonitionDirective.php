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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\AdmonitionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

abstract class AbstractAdmonitionDirective extends SubDirective
{
    public function __construct(private string $name, private string $text)
    {
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    final protected function processSub(
        DocumentNode $document,
        Directive $directive,
    ): Node|null {
        return new AdmonitionNode(
            $this->name,
            $this->text,
            $document->getChildren(),
        );
    }

    final public function getName(): string
    {
        return $this->name;
    }
}
