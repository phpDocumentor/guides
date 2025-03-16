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
use phpDocumentor\Guides\Event\ModifyDocumentEntryAdditionalData;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function is_string;

/** @implements NodeTransformer<Node> */
final class DocumentEntryRegistrationTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface|null $eventDispatcher = null,
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

        $additionalData = [];
        if (is_string($node->getNavigationTitle())) {
            $additionalData['navigationTitle'] = TitleNode::fromString($node->getNavigationTitle());
        }

        if ($this->eventDispatcher !== null) {
            $event = $this->eventDispatcher->dispatch(new ModifyDocumentEntryAdditionalData($additionalData, $node, $compilerContext));
            assert($event instanceof ModifyDocumentEntryAdditionalData);
            $additionalData = $event->getAdditionalData();
        }

        $entry = new DocumentEntryNode(
            $node->getFilePath(),
            $node->getTitle() ?? TitleNode::emptyNode(),
            $node->isRoot(),
            $additionalData,
            $node->isOrphan(),
        );
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
