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

final class Metas
{
    /** @var DocumentEntry[] */
    private array $entries;

    /**
     * @param DocumentEntry[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function addDocument(DocumentEntry $documentEntry): void
    {
        $this->entries[$documentEntry->getFile()] = $documentEntry;
    }

    /**
     * @return DocumentEntry[]
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @param DocumentEntry[] $metaEntries
     */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }

    public function findDocument(string $filePath): ?DocumentEntry
    {
        return $this->entries[$filePath] ?? null;
    }
}
