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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function implode;

/**
 * Renders a raw block, example:
 *
 * .. raw::
 *
 *      <u>Underlined!</u>
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#raw-data-pass-through
 */
final class RawDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'raw';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        return new RawNode(
            implode("\n", $blockContext->getDocumentIterator()->toArray()),
            $directive->getData(),
        );
    }
}
