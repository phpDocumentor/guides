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

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\EntryLegacy;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;

final class Metas
{
    /** @var EntryLegacy[] */
    private array $entries;

    /** @var string[] */
    private array $parents = [];

    /**
     * @param EntryLegacy[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function addDocument(DocumentEntry $documentEntry)
    {
        $this->entries[$documentEntry->getFile()] = $documentEntry;
    }

    /**
     * @return EntryLegacy[]
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    public function get(string $url): ?EntryLegacy
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }

    /**
     * @param EntryLegacy[] $metaEntries
     */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }

    public function findDocument(string $filePath)
    {
        return $this->entries[$filePath] ?? null;
    }
}
