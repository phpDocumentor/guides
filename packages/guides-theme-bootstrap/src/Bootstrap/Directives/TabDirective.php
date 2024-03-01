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

namespace phpDocumentor\Guides\Bootstrap\Directives;

use phpDocumentor\Guides\Bootstrap\Nodes\TabNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function is_string;
use function preg_replace;
use function str_replace;
use function strtolower;

final class TabDirective extends SubDirective
{
    public function getName(): string
    {
        return 'tab';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        if (is_string($directive->getOption('key')->getValue())) {
            $key = strtolower($directive->getOption('key')->getValue());
        } else {
            $key = strtolower($directive->getData());
        }

        $key = str_replace(' ', '-', $key);
        $key = (string) (preg_replace('/[^a-zA-Z0-9\-_]/', '', $key));
        $active = $directive->hasOption('active');

        return new TabNode(
            'tab',
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $key,
            $active,
            $collectionNode->getChildren(),
        );
    }
}
