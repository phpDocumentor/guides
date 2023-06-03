<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\UrlGenerator;
use Psr\Log\LoggerInterface;

use function array_pop;
use function explode;
use function implode;
use function sprintf;
use function str_contains;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
class ReferenceNodeTransformer implements NodeTransformer
{
    public function __construct(
        private readonly Metas $metas,
        private readonly UrlGenerator $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node|null
    {
        if (!$node instanceof SpanNode) {
            return $node;
        }

        foreach ($node->getTokens() as $token) {
            if ($token instanceof ReferenceNode) {
                $this->resolveReference($token, $documentNode);
            } else {
                if ($token instanceof DocReferenceNode) {
                    $this->resolveDocReference($token, $documentNode);
                }
            }
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof SpanNode;
    }

    private function resolveReference(ReferenceNode $referenceNode, DocumentNode $document): void
    {
        $target = $this->metas->getInternalTarget($referenceNode->getReferenceName());
        if ($target === null) {
            $this->logger->warning(
                sprintf(
                    'Reference "%s" could not be resolved',
                    $referenceNode->getReferenceName(),
                ),
                $document->getLoggerInformation(),
            );

            return;
        }

        $referenceNode->setInternalTarget($target);
    }

    private function resolveDocReference(DocReferenceNode $docReferenceNode, DocumentNode $document): void
    {
        $filePath = $this->canonicalUrl($docReferenceNode->getDocumentLink(), $document);

        $documentEntry = $this->metas->findDocument($filePath);
        if ($documentEntry === null) {
            $this->logger->warning(
                sprintf(
                    'Link to document "%s" could not be resolved',
                    $docReferenceNode->getDocumentLink(),
                ),
                $document->getLoggerInformation(),
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
