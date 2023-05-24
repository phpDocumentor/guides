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

use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\Meta\InternalTarget;

use function max;

final class Metas
{
    /** @var array<string, InternalTarget> */
    private array $internalLinkTargets = [];

    /** @var array<string, CitationTarget> */
    private array $citationTargets = [];

    /** @var array<int, FootnoteTarget> */
    private array $footnoteTargets = [];

    private int $maxFootnoteNumber = 0;
    private int $lastReturnedAnonymousFootnoteNumber = -1;

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

    public function addCitationTarget(CitationTarget $target): void
    {
        $this->citationTargets[$target->getName()] = $target;
    }

    public function getCitationTarget(string $name): CitationTarget|null
    {
        return $this->citationTargets[$name] ?? null;
    }

    public function addFootnoteTarget(FootnoteTarget $target): int
    {
        if ($target->getNumber() > 0) {
            $this->maxFootnoteNumber = max($this->maxFootnoteNumber, $target->getNumber());
            $this->footnoteTargets[$target->getNumber()] = $target;
        } else {
            $this->maxFootnoteNumber++;
            $target->setNumber($this->maxFootnoteNumber);
            $target->setAnchorName('footnote-' . $this->maxFootnoteNumber);
            $this->footnoteTargets[$target->getNumber()] = $target;
        }

        return $target->getNumber();
    }

    public function getFootnoteTarget(int $number): FootnoteTarget|null
    {
        return $this->footnoteTargets[$number] ?? null;
    }

    public function getFootnoteTargetByName(string $name): FootnoteTarget|null
    {
        foreach ($this->footnoteTargets as $footnoteTarget) {
            if ($footnoteTarget->getName() === $name) {
                return $footnoteTarget;
            }
        }

        return null;
    }

    public function getFootnoteTargetAnonymous(): FootnoteTarget|null
    {
        foreach ($this->footnoteTargets as $footnoteTarget) {
            if (
                $footnoteTarget->getNumber() > $this->lastReturnedAnonymousFootnoteNumber
                && $footnoteTarget->getName() === ''
            ) {
                $this->lastReturnedAnonymousFootnoteNumber = $footnoteTarget->getNumber();

                return $footnoteTarget;
            }
        }

        return null;
    }

    public function reset(): void
    {
        $this->internalLinkTargets = [];
        $this->entries = [];
        $this->citationTargets = [];
        $this->footnoteTargets = [];
        $this->lastReturnedAnonymousFootnoteNumber = -1;
        $this->maxFootnoteNumber = 0;
    }
}
