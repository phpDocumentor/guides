<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use Exception;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function assert;
use function count;

/** @implements NodeTransformer<MenuEntryNode> */
abstract class AbstractMenuEntryNodeTransformer implements NodeTransformer
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    final public function enterNode(Node $node, CompilerContext $compilerContext): MenuEntryNode
    {
        return $node;
    }

    /** @param MenuEntryNode $node */
    final public function leaveNode(Node $node, CompilerContext $compilerContext): MenuEntryNode|null
    {
        assert($node instanceof MenuEntryNode);
        $currentMenuShaddow = $compilerContext->getShadowTree()->getParent();
        while ($currentMenuShaddow !== null && !$currentMenuShaddow->getNode() instanceof MenuNode) {
            $currentMenuShaddow = $currentMenuShaddow->getParent();
        }

        $currentMenu = $currentMenuShaddow?->getNode();

        if (!$currentMenu instanceof MenuNode) {
            throw new Exception('A MenuEntryNode must be attached to a MenuNode');
        }

        $menuEntries = $this->handleMenuEntry($currentMenu, $node, $compilerContext);

        if (count($menuEntries) === 0) {
            return null;
        }

        if (count($menuEntries) === 1) {
            return $menuEntries[0];
        }

        foreach ($menuEntries as $menuEntry) {
            $compilerContext->getShadowTree()->getParent()?->addChild($menuEntry);
        }

        return null;
    }

    /** @return list<MenuEntryNode> */
    abstract protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContext $compilerContext): array;
}
