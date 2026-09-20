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

namespace phpDocumentor\Guides\Llms\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Llms\Event\ModifyTocPage;
use phpDocumentor\Guides\Llms\Event\ModifyTocProject;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\Renderer\TypeRenderer;
use Psr\EventDispatcher\EventDispatcherInterface;

use function assert;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * The table of contents of a manual as one JSON file.
 *
 * "toc.json" at the root of a rendered manual: what pages there are, in the
 * order the table of contents puts them and nested the way it nests them, each
 * with its title and the anchor a reference to it is built from.
 *
 * A tool that wants to read a manual has to learn its shape first, and nothing
 * published so far says it. "objects.inv.json" comes closest, but it is an
 * index rather than a table of contents: a flat map without order or nesting,
 * which repeats the project title and version in every one of its entries and
 * knows only ".html" addresses. This file states those once, at the top, and
 * leaves each page with what belongs to the page alone. For a manual the size
 * of TYPO3 Explained that is 348 KB against 4.2 MB.
 *
 * Everything here is what any project knows about itself. A theme that knows
 * more -- how its pages are addressed, what else a page carries -- adds it
 * through {@see ModifyTocProject} and {@see ModifyTocPage}.
 */
final class TocRenderer implements TypeRenderer
{
    /** The output format that writes this file. */
    public const FORMAT = 'toc';

    public function __construct(
        private readonly AnchorNormalizer $anchorNormalizer,
        private readonly DocumentOutputFiles $outputFiles,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function render(RenderCommand $renderCommand): void
    {
        $projectNode = $renderCommand->getProjectNode();

        $documents = [];
        foreach ($renderCommand->getDocumentArray() as $document) {
            $documents[$document->getFilePath()] = $document;
        }

        $toc = [
            'project' => $this->project($projectNode),
            'pages' => [$this->describe($projectNode->getRootDocumentEntry(), $documents)],
        ];

        foreach ($this->orphans($projectNode) as $orphan) {
            // Not reachable through any toctree, so the walk above never saw
            // it. Listed all the same: the file is published, and a table of
            // contents that silently drops published pages is worse than one
            // that says where they stand.
            $toc['pages'][] = ['orphan' => true, ...$this->describe($orphan, $documents)];
        }

        $renderCommand->getDestination()->put(
            'toc.json',
            (string) json_encode($toc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }

    /**
     * What the manual says about itself, once.
     *
     * @return array<string, mixed>
     */
    private function project(ProjectNode $projectNode): array
    {
        $event = $this->eventDispatcher->dispatch(new ModifyTocProject(
            [
                'title' => $projectNode->getTitle() ?? '',
                'version' => $projectNode->getVersion() ?? '',
            ],
            $projectNode,
        ));
        assert($event instanceof ModifyTocProject);

        return $event->getProject();
    }

    /**
     * One page and, nested below it, the pages its table of contents leads to.
     *
     * @param array<string, DocumentNode> $documents
     *
     * @return array<string, mixed>
     */
    private function describe(DocumentEntryNode $entry, array $documents): array
    {
        $document = $documents[$entry->getFile()] ?? null;

        $event = $this->eventDispatcher->dispatch(new ModifyTocPage(
            [
                'path' => $entry->getFile(),
                ...$this->outputFiles->of($entry->getFile()),
                'title' => $entry->getTitle()->toString(),
                'anchor' => $this->anchor($entry, $document),
            ],
            $entry,
            $document,
        ));
        assert($event instanceof ModifyTocPage);

        $page = $event->getPage();

        $children = [];
        foreach ($entry->getChildren() as $child) {
            if (!$child instanceof DocumentEntryNode) {
                continue;
            }

            $children[] = $this->describe($child, $documents);
        }

        // Left out rather than written as an empty list: most pages of a large
        // manual are leaves, and the TYPO3 Core Changelog alone would spend
        // 50 KB saying so 3875 times.
        if ($children !== []) {
            $page['pages'] = $children;
        }

        return $page;
    }

    /**
     * The label a reference to the page is built from, or "" when it has none.
     *
     * The explicit label of the first section, the way a ":ref:" to the page
     * names it, and otherwise the id derived from its title -- which the
     * inventory registers as well, so a reference built on it resolves.
     */
    private function anchor(DocumentEntryNode $entry, DocumentNode|null $document): string
    {
        foreach ($document?->getChildren() ?? [] as $child) {
            if (!$child instanceof SectionNode) {
                continue;
            }

            foreach ($child->getChildren() as $sectionChild) {
                if ($sectionChild instanceof AnchorNode) {
                    return $this->anchorNormalizer->reduceAnchor($sectionChild->toString());
                }
            }

            break;
        }

        $id = $entry->getTitle()->getId();

        return $id === '' ? '' : $this->anchorNormalizer->reduceAnchor($id);
    }

    /**
     * Every page no table of contents leads to, in the order the project knows
     * them.
     *
     * @return list<DocumentEntryNode>
     */
    private function orphans(ProjectNode $projectNode): array
    {
        $reached = [];
        $this->collect($projectNode->getRootDocumentEntry(), $reached);

        $orphans = [];
        foreach ($projectNode->getAllDocumentEntries() as $entry) {
            if (isset($reached[$entry->getFile()])) {
                continue;
            }

            $orphans[] = $entry;
        }

        return $orphans;
    }

    /** @param array<string, true> $reached */
    private function collect(DocumentEntryNode $entry, array &$reached): void
    {
        if (isset($reached[$entry->getFile()])) {
            return;
        }

        $reached[$entry->getFile()] = true;
        foreach ($entry->getChildren() as $child) {
            if (!$child instanceof DocumentEntryNode) {
                continue;
            }

            $this->collect($child, $reached);
        }
    }
}
