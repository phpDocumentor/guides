<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\ReplacementNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function count;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
class ReplaceDirective extends SubDirective
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'replace';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    final protected function processSub(
        DocumentNode $document,
        Directive $directive,
    ): Node|null {
        /** @var array<InlineNode> $children */
        $children = $document->getChildren();
        $data = $directive->getDataNode();
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
