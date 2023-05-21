<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\CompoundNode;
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
 * Resolves reference tokens in span nodes
 *
 * This follows the reStructuredText rules as outlined in:
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
 */
class ReferenceResolverPass implements CompilerPass
{
    public function getPriority(): int
    {
        return 500; // must be run *after* MetasPass
    }

    public function __construct(
        private readonly Metas $metas,
        private readonly UrlGenerator $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** {@inheritDoc} */
    public function run(array $documents): array
    {
        foreach ($documents as $document) {
            $this->traverse($document, $document->getFilePath(), $document);
        }

        return $documents;
    }

    private function traverse(Node $node, string $path, DocumentNode $document): void
    {
        if (!($node instanceof CompoundNode)) {
            return;
        }

        foreach ($node->getChildren() as $child) {
            if ($child instanceof SpanNode) {
                foreach ($child->getTokens() as $token) {
                    if ($token instanceof ReferenceNode) {
                        $this->resolveReference($token, $document);
                    } else {
                        if ($token instanceof DocReferenceNode) {
                            $this->resolveDocReference($token, $document);
                        }
                    }
                }
            } elseif ($child instanceof CompoundNode) {
                $this->traverse($child, $path, $document);
            }
        }
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
}
