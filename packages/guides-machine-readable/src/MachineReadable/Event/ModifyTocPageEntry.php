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

namespace phpDocumentor\Guides\MachineReadable\Event;

use phpDocumentor\Guides\MachineReadable\Toc\PageDescriptor;
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
 * The page's own children are not in the descriptor yet: they are added after
 * this event, so that a listener cannot be surprised by a page it did not put
 * there. {@see ModifyTocPageEntry::getDocumentEntry()} still reaches them, and
 * each of them is dispatched in turn.
 */
final class ModifyTocPageEntry
{
    /**
     * @param DocumentNode|null $document the parsed page, absent when the
     *     table of contents names a page that was not rendered in this run
     */
    public function __construct(
        private readonly PageDescriptor $page,
        private readonly DocumentEntryNode $documentEntry,
        private readonly DocumentNode|null $document,
    ) {
    }

    public function getPage(): PageDescriptor
    {
        return $this->page;
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
