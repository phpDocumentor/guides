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

namespace phpDocumentor\Guides\Bootstrap\Compiler;

use phpDocumentor\Guides\Bootstrap\Nodes\AccordionItemNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardFooterNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardHeaderNode;
use phpDocumentor\Guides\Bootstrap\Nodes\Card\CardImageNode;
use phpDocumentor\Guides\Bootstrap\Nodes\CardNode;
use phpDocumentor\Guides\Compiler\CallableAccessor;
use phpDocumentor\Guides\Compiler\SubNodeAccessorProvider;
use phpDocumentor\Guides\Compiler\SubNodeAccessorRegistry;
use phpDocumentor\Guides\Compiler\SubnodeList;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\TitleNode;

final class BootstrapProvider implements SubNodeAccessorProvider
{
    public function register(SubNodeAccessorRegistry $registry): void
    {
        $registry->register(AccordionItemNode::class, new SubnodeList([
            new CallableAccessor('title', static fn (AccordionItemNode $node) => $node->getTitle(), static fn (AccordionItemNode $node, TitleNode $value) => $node->withTitle($value)),
            new CallableAccessor('content', static fn (AccordionItemNode $node) => $node->getContent(), static fn (AccordionItemNode $node, InlineCompoundNode $value) => $node->withContent($value)),
        ]));
        $registry->register(CardNode::class, new SubnodeList([
            new CallableAccessor('header', static fn (CardNode $node) => $node->getCardHeader(), static fn (CardNode $node, CardHeaderNode|null $value) => $node->withCardHeader($value), nullable: true),
            new CallableAccessor('image', static fn (CardNode $node) => $node->getCardImage(), static fn (CardNode $node, CardImageNode|null $value) => $node->withCardImage($value), nullable: true),
            new CallableAccessor('title', static fn (CardNode $node) => $node->getTitle(), static fn (CardNode $node, TitleNode|null $value) => $node->withTitle($value), nullable: true),
            new CallableAccessor('footer', static fn (CardNode $node) => $node->getCardFooter(), static fn (CardNode $node, CardFooterNode|null $value) => $node->withCardFooter($value), nullable: true),
            new CallableAccessor('content', static fn (CardNode $node) => $node->getContent(), static fn (CardNode $node, InlineCompoundNode $value) => $node->withContent($value)),
        ]));
    }
}
