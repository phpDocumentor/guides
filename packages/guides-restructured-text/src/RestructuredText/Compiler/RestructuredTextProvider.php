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

namespace phpDocumentor\Guides\RestructuredText\Compiler;

use phpDocumentor\Guides\Compiler\CallableAccessor;
use phpDocumentor\Guides\Compiler\ListAccessor;
use phpDocumentor\Guides\Compiler\SubNodeAccessorProvider;
use phpDocumentor\Guides\Compiler\SubNodeAccessorRegistry;
use phpDocumentor\Guides\Compiler\SubnodeList;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Nodes\ConfvalNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\SidebarNode;

final class RestructuredTextProvider implements SubNodeAccessorProvider
{
    public function register(SubNodeAccessorRegistry $registry): void
    {
        $registry->register(GeneralDirectiveNode::class, new SubnodeList([
            new CallableAccessor('content', static fn (GeneralDirectiveNode $node) => $node->getContent(), static fn (GeneralDirectiveNode $node, InlineCompoundNode $value) => $node->withContent($value)),
        ]));
        $registry->register(SidebarNode::class, new SubnodeList([
            new CallableAccessor('title', static fn (SidebarNode $node) => $node->getTitle(), static fn (SidebarNode $node, InlineCompoundNode $value) => $node->withTitle($value)),
        ]));
        $registry->register(ConfvalNode::class, new SubnodeList([
            new CallableAccessor('type', static fn (ConfvalNode $node) => $node->getType(), static fn (ConfvalNode $node, InlineCompoundNode|null $value) => $node->withType($value), nullable: true),
            new CallableAccessor('default', static fn (ConfvalNode $node) => $node->getDefault(), static fn (ConfvalNode $node, InlineCompoundNode|null $value) => $node->withDefault($value), nullable: true),
            new ListAccessor('options', static fn (ConfvalNode $node) => $node->getAdditionalOptions(), static fn (ConfvalNode $node, array $value) => $node->withAdditionalOptions(self::onlyInline($value))),
        ]));
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<string, InlineCompoundNode>
     */
    private static function onlyInline(array $options): array
    {
        $result = [];
        foreach ($options as $key => $option) {
            if (!($option instanceof InlineCompoundNode)) {
                continue;
            }

            $result[(string) $key] = $option;
        }

        return $result;
    }
}
