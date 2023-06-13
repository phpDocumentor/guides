<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\UrlGenerator;
use Psr\Log\LoggerInterface;

use function array_pop;
use function explode;
use function implode;
use function sprintf;
use function str_contains;

/** @implements NodeTransformer<Node> */
class ReferenceNodeTransformer implements NodeTransformer
{
    public function __construct(
        private readonly UrlGenerator $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof ReferenceNode) {
            $this->resolveReference($node, $compilerContext);
        } else {
            if ($node instanceof DocReferenceNode) {
                $this->resolveDocReference($node, $compilerContext);
            }
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof ReferenceNode || $node instanceof DocReferenceNode;
    }

    private function resolveReference(ReferenceNode $referenceNode, CompilerContext $compilerContext): void
    {
        $target = $compilerContext->getProjectNode()->getInternalTarget($referenceNode->getReferenceName());
        if ($target === null) {
            $this->logger->warning(
                sprintf(
                    'Reference "%s" could not be resolved',
                    $referenceNode->getReferenceName(),
                ),
                $compilerContext->getDocumentNode()->getLoggerInformation(),
            );

            return;
        }

        $referenceNode->setInternalTarget($target);
    }

    private function resolveDocReference(DocReferenceNode $docReferenceNode, CompilerContext $compilerContext): void
    {
        $filePath = $this->canonicalUrl($docReferenceNode->getDocumentLink(), $compilerContext->getDocumentNode());

        $documentEntry = $compilerContext->getProjectNode()->findDocumentEntry($filePath);
        if ($documentEntry === null) {
            $this->logger->warning(
                sprintf(
                    'Link to document "%s" could not be resolved',
                    $docReferenceNode->getDocumentLink(),
                ),
                $compilerContext->getDocumentNode()->getLoggerInformation(),
            );

            return;
        }

        $docReferenceNode->setDocumentEntry($documentEntry);
    }

    private function canonicalUrl(string $url, DocumentNode $document): string
    {
        // extract path without filename
        $path = $document->getFilePath();
        if (str_contains($path, '/')) {
            $parts = explode('/', $path);
            array_pop($parts);
            $path = implode('/', $parts);
        } else {
            $path = '';
        }

        return $this->urlGenerator->canonicalUrl($path, $url);
    }

    public function getPriority(): int
    {
        // After CollectLinkTargetsTransformer
        return 3000;
    }
}
