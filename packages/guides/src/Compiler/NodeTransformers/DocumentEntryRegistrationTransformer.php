<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
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

        $title = $node->getTitle()?->toString();
        if ($title === null) {
            $this->logger->warning('Document has not title', $node->getLoggerInformation());
        }

        $entry = new DocumentEntryNode($node->getFilePath(), $title, $node);
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
