<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use Exception;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function assert;

class SubInternalMenuEntryNodeTransformer extends AbstractMenuEntryNodeTransformer
{
    // Setting a default level prevents PHP errors in case of circular references
    private const DEFAULT_MAX_LEVELS = 10;

    public function __construct(
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode || $node instanceof NavMenuNode || $node instanceof InternalMenuEntryNode;
    }


    protected function handleMenuEntry(MenuNode $currentMenu, MenuEntryNode $node, CompilerContext $compilerContext): array
    {
        assert($node instanceof InternalMenuEntryNode);
        $maxDepth = (int) $currentMenu->getOption('maxdepth', self::DEFAULT_MAX_LEVELS);
        $documentEntryOfMenuEntry = $compilerContext->getProjectNode()->getDocumentEntry($node->getUrl());
        $this->addSubEntries($compilerContext, $node, $documentEntryOfMenuEntry, $node->getLevel() + 1, $maxDepth);

        return [$node];
    }

    public function getPriority(): int
    {
        // After MenuEntries are resolved
        return 3000;
    }

    private function addSubEntries(
        CompilerContext $compilerContext,
        InternalMenuEntryNode $sectionMenuEntry,
        DocumentEntryNode $documentEntry,
        int $currentLevel,
        int $maxDepth,
    ): void {
        if ($maxDepth < $currentLevel) {
            return;
        }

        foreach ($documentEntry->getChildren() as $subDocumentEntryNode) {
            $subMenuEntry = new InternalMenuEntryNode(
                $subDocumentEntryNode->getFile(),
                $subDocumentEntryNode->getTitle(),
                [],
                false,
                $currentLevel,
                '',
                self::isInRootline($subDocumentEntryNode, $compilerContext->getDocumentNode()->getDocumentEntry()),
                self::isCurrent($subDocumentEntryNode, $compilerContext->getDocumentNode()->getFilePath()),
            );
            $sectionMenuEntry->addMenuEntry($subMenuEntry);
            $this->addSubEntries($compilerContext, $subMenuEntry, $subDocumentEntryNode, $currentLevel + 1, $maxDepth);
        }
    }
}
