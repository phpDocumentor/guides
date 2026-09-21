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

namespace phpDocumentor\Guides\Compiler\Passes\IndexCollector;

use phpDocumentor\Guides\Exception\DuplicateLinkAnchorException;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use Psr\Log\LoggerInterface;

/**
 * Makes a named `.. index::` block a target a `:ref:` can link to.
 *
 * A `:name:` on the directive names the place the entries are filed under, so
 * the target points where the index does: at the section that follows the
 * block, or the document's own title when none does -- the same answer
 * {@see IndexEntryCollector::placements()} gives the index itself.
 *
 * This cannot be left to the anchor handling a `.. _label:` gets. That runs
 * while the tree still holds the unprocessed directive, well before the
 * directive has been turned into an index node that could carry an anchor.
 */
final class IndexTargetRegistrar
{
    public function __construct(
        private readonly IndexEntryCollector $collector,
        private readonly AnchorNormalizer $anchorNormalizer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @param DocumentNode[] $documents */
    public function registerAll(array $documents, ProjectNode $projectNode): void
    {
        foreach ($documents as $document) {
            foreach ($this->collector->placements($document) as [$node, , $anchor, $title]) {
                $name = $node->getName();
                if ($name === null) {
                    continue;
                }

                try {
                    $projectNode->addLinkTarget(
                        $this->anchorNormalizer->reduceAnchor($name),
                        new InternalTarget($document->getFilePath(), $anchor ?? '', $title),
                    );
                } catch (DuplicateLinkAnchorException $exception) {
                    $this->logger->warning($exception->getMessage(), ['rst-file' => $document->getFilePath()]);
                }
            }
        }
    }
}
