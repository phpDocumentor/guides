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
use phpDocumentor\Guides\Meta\InternalTarget;

final class Metas
{
    /** @var array<string, InternalTarget> */
    private array $internalLinkTargets = [];

    /** @param DocumentEntry[] $entries */
    public function __construct(private array $entries = [])
    {
    }

    public function addDocument(DocumentEntry $documentEntry): void
    {
        $this->entries[$documentEntry->getFile()] = $documentEntry;
    }

    /** @return DocumentEntry[] */
    public function getAll(): array
    {
        return $this->entries;
    }

    /** @param DocumentEntry[] $metaEntries */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }

    public function findDocument(string $filePath): DocumentEntry|null
    {
        return $this->entries[$filePath] ?? null;
    }

    public function addLinkTarget(string $anchorName, InternalTarget $target): void
    {
        $this->internalLinkTargets[$anchorName] = $target;
    }

    public function getInternalTarget(string $anchorName): InternalTarget|null
    {
        return $this->internalLinkTargets[$anchorName] ?? null;
    }

    /** @return array<string, InternalTarget> */
    public function getAllInternalTargets(): array
    {
        return $this->internalLinkTargets;
    }

    public function reset(): void
    {
        $this->internalLinkTargets = [];
        $this->entries = [];
    }
}
