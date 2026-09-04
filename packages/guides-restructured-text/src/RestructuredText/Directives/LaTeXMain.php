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

use phpDocumentor\Guides\Nodes\MainNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;

/**
 * Marks the document as LaTeX main
 */
#[Attributes\Directive(name: 'latex-main')]
final class LaTeXMain extends BaseDirective
{
    public function createNode(DirectiveNode $directiveNode): Node
    {
        return new MainNode($directiveNode->getDirective()->getData());
    }
}
