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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use Psr\Log\LoggerInterface;

/** @implements NodeTransformer<Node> */
final class DocumentEntryRegistrationTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if (!$node instanceof DocumentNode) {
            return $node;
        }

        if ($node->getTitle() === null && !$node->isOrphan()) {
            $this->logger->warning('Document has no title', $compilerContext->getLoggerInformation());
        }

        $entry = new DocumentEntryNode($node->getFilePath(), $node->getTitle() ?? TitleNode::emptyNode(), $node->isRoot());
        $compilerContext->getProjectNode()->addDocumentEntry($entry);

        return $node->setDocumentEntry($entry);
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode;
    }

    public function getPriority(): int
    {
        // Before TocNodeWithDocumentEntryTransformer and SectionEntryRegistrationTransformer
        return 5000;
    }
}
