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

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\MultipleLinkTargetsNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use Psr\Log\LoggerInterface;
use SplStack;
use Webmozart\Assert\Assert;

use function sprintf;

/** @implements NodeTransformer<DocumentNode|AnchorNode|SectionNode> */
final class CollectLinkTargetsTransformer implements NodeTransformer
{
    /** @var SplStack<DocumentNode> */
    private readonly SplStack $documentStack;

    public function __construct(
        private readonly AnchorNormalizer $anchorReducer,
        private LoggerInterface|null $logger = null,
    ) {
        /*
         * TODO: remove stack here, as we should not have sub documents in this way, sub documents are
         *       now produced by the {@see \phpDocumentor\Guides\RestructuredText\MarkupLanguageParser::getSubParser}
         *       as this works right now in isolation includes do not work as they should.
         */
        $this->documentStack = new SplStack();
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->push($node);

            return $node;
        }

        if ($node instanceof AnchorNode) {
            $currentDocument = $compilerContext->getDocumentNode();
            $parentSection = $compilerContext->getShadowTree()->getParent()?->getNode();
            $title = null;
            if ($parentSection instanceof SectionNode) {
                $title = $parentSection->getTitle()->toString();
            }

            $anchorName = $this->anchorReducer->reduceAnchor($node->toString());
            $compilerContext->getProjectNode()->addLinkTarget(
                $anchorName,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $node->toString(),
                    $title,
                ),
            );

            return $node;
        }

        if ($node instanceof SectionNode) {
            $currentDocument = $this->documentStack->top();
            Assert::notNull($currentDocument);
            $anchorName = $node->getId();
            $compilerContext->getProjectNode()->addLinkTarget(
                $anchorName,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $anchorName,
                    $node->getLinkText(),
                    $node->getLinkType(),
                ),
            );

            return $node;
        }

        if ($node instanceof LinkTargetNode) {
            $currentDocument = $this->documentStack->top();
            Assert::notNull($currentDocument);
            $anchor = $this->anchorReducer->reduceAnchor($node->getId());
            $this->addLinkTargetToProject(
                $compilerContext,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $anchor,
                    $node->getLinkText(),
                    $node->getLinkType(),
                ),
            );
            if ($node instanceof MultipleLinkTargetsNode) {
                foreach ($node->getAdditionalIds() as $id) {
                    $anchor = $this->anchorReducer->reduceAnchor($id);
                    $this->addLinkTargetToProject(
                        $compilerContext,
                        new InternalTarget(
                            $currentDocument->getFilePath(),
                            $anchor,
                            $node->getLinkText(),
                            $node->getLinkType(),
                        ),
                    );
                }
            }
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->pop();
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof AnchorNode || $node instanceof LinkTargetNode;
    }

    public function getPriority(): int
    {
        // After MetasPass
        return 5000;
    }

    private function addLinkTargetToProject(CompilerContext $compilerContext, InternalTarget $internalTarget): void
    {
        if ($compilerContext->getProjectNode()->hasInternalTarget($internalTarget->getAnchor(), $internalTarget->getLinkType())) {
            $otherLink = $compilerContext->getProjectNode()->getInternalTarget($internalTarget->getAnchor(), $internalTarget->getLinkType());
            $this->logger?->warning(
                sprintf(
                    'Duplicate anchor "%s" for link type "%s" in document "%s". The anchor is already used at "%s"',
                    $internalTarget->getAnchor(),
                    $internalTarget->getLinkType(),
                    $compilerContext->getDocumentNode()->getFilePath(),
                    $otherLink?->getDocumentPath(),
                ),
                $compilerContext->getLoggerInformation(),
            );

            return;
        }

        $compilerContext->getProjectNode()->addLinkTarget(
            $internalTarget->getAnchor(),
            $internalTarget,
        );
    }
}
