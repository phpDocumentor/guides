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

namespace phpDocumentor\Guides\Compiler\Parallel;

use phpDocumentor\Guides\Exception\DuplicateLinkAnchorException;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;

/**
 * Container for per-batch compilation results during parallel collection phase.
 *
 * During parallel compilation, each child process runs transformers on its batch
 * of documents. The transformers write to the child's copy of ProjectNode (via
 * copy-on-write from the fork). Before the child exits, this class extracts
 * all the data that was added to ProjectNode so it can be serialized back to
 * the parent process for merging.
 *
 * This enables parallel collection by deferring shared state mutations:
 * 1. Parallel Collection: Each child runs transformers, populating its ProjectNode copy
 * 2. Extract: Child extracts ProjectNode data into DocumentCompilationResult
 * 3. Sequential Merge: Parent merges all results into the real ProjectNode (fast, O(n))
 * 4. Parallel Resolution: With complete ProjectNode, resolve cross-references
 */
final class DocumentCompilationResult
{
    /**
     * Document entries collected from ProjectNode.
     *
     * @var DocumentEntryNode[]
     */
    public array $documentEntries = [];

    /**
     * Internal link targets collected from ProjectNode.
     *
     * @var array<string, array<string, InternalTarget>>
     */
    public array $internalLinkTargets = [];

    /**
     * Citation targets collected from ProjectNode.
     *
     * @var array<string, CitationTarget>
     */
    public array $citationTargets = [];

    /**
     * Any warnings or errors collected during processing.
     *
     * @var list<array{level: string, message: string}>
     */
    public array $messages = [];

    /**
     * Toctree relationships as path-based data (serialization-safe).
     *
     * Stored as path strings instead of object references to survive serialization
     * across process boundaries during parallel compilation.
     *
     * Structure: [documentPath => ['children' => array, 'parent' => string|null]]
     * Children can be:
     *   - ['type' => 'document', 'path' => string] for DocumentEntryNode
     *   - ['type' => 'external', 'url' => string, 'title' => string] for ExternalEntryNode
     *
     * @var array<string, array{children: list<array{type: string, path?: string, url?: string, title?: string}>, parent: string|null}>
     */
    public array $toctreeRelationships = [];

    /**
     * Extract all relevant data from a ProjectNode after running collection transformers.
     *
     * This is called in the child process after transformers have run, to capture
     * all the data that was added to the child's copy of ProjectNode.
     */
    public static function extractFromProjectNode(ProjectNode $projectNode): self
    {
        $result = new self();

        // Extract document entries (keyed by file path)
        $result->documentEntries = $projectNode->getAllDocumentEntries();

        // Extract internal link targets
        $result->internalLinkTargets = $projectNode->getAllInternalTargets();

        // Extract citation targets
        $result->citationTargets = $projectNode->getAllCitationTargets();

        // Extract toctree relationships as path-based data (serialization-safe)
        $result->toctreeRelationships = self::extractToctreeRelationships($result->documentEntries);

        return $result;
    }

    /**
     * Extract toctree parent/child relationships as path-based data.
     *
     * Object references don't survive serialization across process boundaries,
     * so we convert them to path strings that can be resolved later.
     *
     * @param DocumentEntryNode[] $documentEntries
     *
     * @return array<string, array{children: list<array{type: string, path?: string, url?: string, title?: string}>, parent: string|null}>
     */
    private static function extractToctreeRelationships(array $documentEntries): array
    {
        $relationships = [];

        foreach ($documentEntries as $entry) {
            $path = $entry->getFile();

            // Extract children as path-based references
            $children = [];
            foreach ($entry->getMenuEntries() as $child) {
                if ($child instanceof DocumentEntryNode) {
                    $children[] = [
                        'type' => 'document',
                        'path' => $child->getFile(),
                    ];
                } elseif ($child instanceof ExternalEntryNode) {
                    $children[] = [
                        'type' => 'external',
                        'url' => $child->getValue(),
                        'title' => $child->getTitle(),
                    ];
                }
            }

            // Extract parent as path reference
            $parent = $entry->getParent();
            $parentPath = $parent instanceof DocumentEntryNode ? $parent->getFile() : null;

            $relationships[$path] = [
                'children' => $children,
                'parent' => $parentPath,
            ];
        }

        return $relationships;
    }

    /**
     * Merge this result into a ProjectNode.
     *
     * Called by the parent process to merge child results into the real ProjectNode.
     */
    public function mergeIntoProjectNode(ProjectNode $projectNode): void
    {
        // Merge document entries
        foreach ($this->documentEntries as $entry) {
            $projectNode->addDocumentEntry($entry);
        }

        // Merge internal link targets
        foreach ($this->internalLinkTargets as $targets) {
            foreach ($targets as $anchor => $target) {
                try {
                    // Cast to string as PHP converts numeric string keys to int
                    $projectNode->addLinkTarget((string) $anchor, $target);
                } catch (DuplicateLinkAnchorException) {
                    // Ignore duplicates - first writer wins
                }
            }
        }

        // Merge citation targets
        foreach ($this->citationTargets as $target) {
            $projectNode->addCitationTarget($target);
        }
    }

    /**
     * Add a message (warning/error) collected during processing.
     */
    public function addMessage(string $level, string $message): void
    {
        $this->messages[] = ['level' => $level, 'message' => $message];
    }
}
