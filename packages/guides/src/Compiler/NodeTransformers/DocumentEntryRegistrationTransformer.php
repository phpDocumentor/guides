<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use Psr\Log\LoggerInterface;

/** @implements NodeTransformer<Node> */
class DocumentEntryRegistrationTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof DocumentNode) {
            return $node;
        }

        if ($node->getTitle() === null) {
            $this->logger->warning('Document has not title', $node->getLoggerInformation());
        }

        $entry = new DocumentEntryNode($node->getFilePath(), $node->getTitle() ?? TitleNode::emptyNode());
        $compilerContext->getProjectNode()->addDocumentEntry($entry);

        return $node->withDocumentEntry($entry);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode;
    }

    public function getPriority(): int
    {
        // Before MenuNodeTransformer
        return 5000;
    }
}
