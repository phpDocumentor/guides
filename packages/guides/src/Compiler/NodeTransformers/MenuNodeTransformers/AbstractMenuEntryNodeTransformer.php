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

/** @implements NodeTransformer<MenuNode|MenuEntryNode> */
abstract class AbstractMenuEntryNodeTransformer implements NodeTransformer
{
    private MenuNode|null $currentMenu = null;

    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    final public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof MenuNode) {
            $this->currentMenu = $node;
        }

        return $node;
    }

    final public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof MenuNode) {
            $this->currentMenu = null;

            return $node;
        }

        if ($this->currentMenu === null) {
            throw new Exception('A MenuEntryNode must be attached to a MenuNode');
        }

        assert($node instanceof MenuEntryNode);

        $menuEntries = $this->handleMenuEntry($this->currentMenu, $node, $compilerContext);

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
    abstract protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $node, CompilerContext $compilerContext): array;
}
