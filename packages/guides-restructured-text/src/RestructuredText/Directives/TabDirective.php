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
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\TabNode;

use function class_alias;
use function class_exists;
use function is_string;
use function preg_replace;
use function str_replace;
use function strtolower;

#[Attributes\Directive(name: 'tab')]
final class TabDirective extends SubDirective
{
    public function createNode(DirectiveNode $directiveNode): Node
    {
        $directive = $directiveNode->getDirective();

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
            $directiveNode->getChildren(),
        );
    }
}

if (!class_exists(\phpDocumentor\Guides\Bootstrap\Directives\TabDirective::class, false)) {
    class_alias(TabDirective::class, \phpDocumentor\Guides\Bootstrap\Directives\TabDirective::class);
}
