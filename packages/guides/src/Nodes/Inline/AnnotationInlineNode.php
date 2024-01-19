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

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Meta\InternalTarget;

/**
 * This node an annotation, for example citation or footnote
 */
abstract class AnnotationInlineNode extends InlineNode
{
    protected InternalTarget|null $internalTarget = null;

    abstract public function getName(): string;

    public function getInternalTarget(): InternalTarget|null
    {
        return $this->internalTarget;
    }

    public function setInternalTarget(InternalTarget|null $internalTarget): void
    {
        $this->internalTarget = $internalTarget;
    }
}
