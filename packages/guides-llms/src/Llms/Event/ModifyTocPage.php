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

namespace phpDocumentor\Guides\Llms\Event;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;

/**
 * One page of "toc.json", before it is written.
 *
 * Dispatched for every page, the orphans included, with what the renderer
 * could say about it on its own: where it is, which files it was rendered to,
 * its title and its anchor. A theme that knows more about a page -- a
 * permalink, a deprecation, a content type -- adds it here.
 *
 * The page's own children are not in the array yet: they are nested under the
 * "pages" key after this event, so that a listener cannot be surprised by a
 * key it did not put there, and so that the key a reader scrolls past last is
 * the one that opens a subtree. {@see ModifyTocPage::getDocumentEntry()} still
 * reaches them, and each of them is dispatched in turn.
 */
final class ModifyTocPage
{
    /**
     * @param array<string, mixed> $page
     * @param DocumentNode|null $document the parsed page, absent when the
     *     table of contents names a page that was not rendered in this run
     */
    public function __construct(
        private array $page,
        private readonly DocumentEntryNode $documentEntry,
        private readonly DocumentNode|null $document,
    ) {
    }

    /** @return array<string, mixed> */
    public function getPage(): array
    {
        return $this->page;
    }

    /** @param array<string, mixed> $page */
    public function setPage(array $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getDocumentEntry(): DocumentEntryNode
    {
        return $this->documentEntry;
    }

    public function getDocument(): DocumentNode|null
    {
        return $this->document;
    }
}
