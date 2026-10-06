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
use phpDocumentor\Guides\Nodes\ConsumesDefaultHighlightLanguage;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\HighlightNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Settings\SettingsManager;

/**
 * Applies the default highlight language, as set by the project settings or the reStructuredText
 * `.. highlight::` directive, to every node implementing {@see ConsumesDefaultHighlightLanguage} that does not
 * already have an explicit language.
 *
 * The {@see HighlightNode} produced by the highlight directive is a marker only, it is removed from the document
 * tree by this transformer.
 *
 * @implements NodeTransformer<Node>
 */
final class HighlightNodeTransformer implements NodeTransformer
{
    private DocumentNode|null $currentDocument = null;
    private string|null $currentLanguage = null;

    public function __construct(
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        $this->resetStateForNewDocument($compilerContext);

        if ($node instanceof HighlightNode) {
            $this->currentLanguage = $node->getLanguage();

            return $node;
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if ($node instanceof HighlightNode) {
            return null;
        }

        if ($node instanceof ConsumesDefaultHighlightLanguage && $node->getLanguage() === null) {
            $node->setLanguage($this->currentLanguage);
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof HighlightNode || $node instanceof ConsumesDefaultHighlightLanguage;
    }

    public function getPriority(): int
    {
        return 90;
    }

    private function resetStateForNewDocument(CompilerContextInterface $compilerContext): void
    {
        $documentNode = $compilerContext->getDocumentNode();
        if ($documentNode->getHash() === $this->currentDocument?->getHash()) {
            return;
        }

        $this->currentDocument = $documentNode;
        $this->currentLanguage = null;
        if ($this->settingsManager->getProjectSettings()->getDefaultCodeLanguage() === '') {
            return;
        }

        $this->currentLanguage = $this->settingsManager->getProjectSettings()->getDefaultCodeLanguage();
    }
}
