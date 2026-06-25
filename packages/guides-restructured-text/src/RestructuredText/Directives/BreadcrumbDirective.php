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

use phpDocumentor\Guides\Nodes\BreadCrumbNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * The "breadcrumb" directive displays a breadcrumb of the current. It does not exist in Sphinx or the
 * reST standard yet.
 *
 * It takes neither arguments nor content.
 *
 * Usage:
 *
 * ```
 * ..  breadcrumb::
 * ```
 */
#[Attributes\Directive(name: 'breadcrumb')]
final class BreadcrumbDirective extends BaseDirective
{
    public function createNode(DirectiveNode $directiveNode): Node|null
    {
        return new BreadCrumbNode();
    }
}
