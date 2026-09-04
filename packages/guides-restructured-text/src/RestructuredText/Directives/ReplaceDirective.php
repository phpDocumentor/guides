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

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\ReplacementNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;

use function count;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
#[Attributes\Directive(name: 'replace')]
final class ReplaceDirective extends SubDirective
{
    public function createNode(DirectiveNode $directiveNode): Node
    {
        /** @var array<InlineCompoundNode> $children */
        $children = $directiveNode->getChildren();
        $data = $directiveNode->getDirective()->getDataNode();
        if ($data !== null) {
            if (count($children) > 0) {
                $children[] = new ParagraphNode([$data]);
            } else {
                $children[] = $data;
            }
        }

        return new ReplacementNode(
            $children,
        );
    }
}
