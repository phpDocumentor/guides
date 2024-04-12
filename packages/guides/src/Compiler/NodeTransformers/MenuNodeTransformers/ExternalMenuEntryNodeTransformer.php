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

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ExternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use Psr\Log\LoggerInterface;

use function assert;

final class ExternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    use MenuEntryManagement;
    use SubSectionHierarchyHandler;

    public function __construct(
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof ExternalMenuEntryNode;
    }

    /** @return list<MenuEntryNode> */
    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $entryNode, CompilerContextInterface $compilerContext): array
    {
        assert($entryNode instanceof ExternalMenuEntryNode);

        $newEntryNode = new ExternalEntryNode(
            $entryNode->getUrl(),
            ($entryNode->getValue() ?? TitleNode::emptyNode())->toString(),
        );

        if ($currentMenu instanceof TocNode) {
            $this->attachDocumentEntriesToParents([$newEntryNode], $compilerContext, '');
        }

        return [$entryNode];
    }

    public function getPriority(): int
    {
        // After DocumentEntryTransformer
        return 4500;
    }
}
