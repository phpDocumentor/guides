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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\AdmonitionNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_filter;

final class CoreProvider implements SubNodeAccessorProvider
{
    public function register(SubNodeAccessorRegistry $registry): void
    {
        $registry->register(DefinitionListItemNode::class, new SubnodeList([
            new CallableAccessor('term', static fn (DefinitionListItemNode $node) => $node->getTerm(), static fn (DefinitionListItemNode $node, InlineCompoundNode $value) => $node->withTerm($value)),
            new CallableListField('classifiers', static fn (DefinitionListItemNode $node) => $node->getClassifiers(), static fn (DefinitionListItemNode $node, array $value) => $node->withClassifiers(array_filter($value, static fn (mixed $item): bool => $item instanceof InlineCompoundNode))),
        ]));
        $registry->register(CodeNode::class, new SubnodeList([
            new CallableAccessor('caption', static fn (CodeNode $node) => $node->getCaption(), static function (CodeNode $node, InlineCompoundNode|null $value): CodeNode {
                $result = clone $node;
                $result->setCaption($value);

                return $result;
            }, nullable: true),
        ]));
        $registry->register(AdmonitionNode::class, new SubnodeList([
            new CallableAccessor('title', static fn (AdmonitionNode $node) => $node->getTitle(), static fn (AdmonitionNode $node, InlineCompoundNode|null $value) => $node->withTitle($value), nullable: true),
        ]));
        $registry->register(FigureNode::class, new SubnodeList([
            new CallableAccessor('image', static fn (FigureNode $node) => $node->getImage(), static fn (FigureNode $node, ImageNode $value) => $node->withImage($value)),
            new CallableAccessor('document', static fn (FigureNode $node) => $node->getDocument(), static fn (FigureNode $node, Node|null $value) => $node->withDocument($value), nullable: true),
        ]));
        $registry->register(ImageNode::class, new SubnodeList([
            new CallableAccessor('target', static fn (ImageNode $node) => $node->getTarget(), static function (ImageNode $node, LinkInlineNode|null $value): ImageNode {
                $result = clone $node;
                $result->setTarget($value);

                return $result;
            }, nullable: true),
        ]));
        $registry->register(MenuNode::class, new SubnodeList([
            new CallableAccessor('caption', static fn (MenuNode $node) => $node->getCaption(), static fn (MenuNode $node, InlineCompoundNode|null $value) => $node->withCaption($value), nullable: true),
        ]));
    }
}
