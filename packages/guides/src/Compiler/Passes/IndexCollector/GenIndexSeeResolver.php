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

use phpDocumentor\Guides\Nodes\Index\GenIndexRowKind;

use function mb_strtolower;
use function trim;

/**
 * Points every `see`/`seealso` row at the anchor of the term it targets.
 *
 * @phpstan-import-type GenIndexTermMap from IndexEntryCollector
 * @phpstan-import-type GenIndexTermData from IndexEntryCollector
 * @phpstan-import-type GenIndexRowData from IndexEntryCollector
 */
final class GenIndexSeeResolver
{
    /** @param GenIndexTermMap $termMap */
    public function resolveSeeRows(array &$termMap): void
    {
        foreach ($termMap as $key => $term) {
            $termMap[$key]['rows'] = $this->resolveSeeRowTargets($term['rows'], $termMap);
            foreach ($term['subterms'] as $subKey => $subterm) {
                $termMap[$key]['subterms'][$subKey]['rows'] = $this->resolveSeeRowTargets($subterm['rows'], $termMap);
            }
        }
    }

    /**
     * @param array<int, GenIndexRowData> $rows
     * @param GenIndexTermMap $termMap
     *
     * @return array<int, GenIndexRowData>
     */
    private function resolveSeeRowTargets(array $rows, array $termMap): array
    {
        foreach ($rows as $index => $row) {
            if ($row['kind'] === GenIndexRowKind::Link) {
                continue;
            }

            $targetKey = $this->normalize($row['seeText'] ?? '');
            $targetLinkRow = $this->findFirstLinkRow($termMap[$targetKey] ?? null);
            if ($targetLinkRow === null) {
                continue;
            }

            $rows[$index]['anchor'] = $targetLinkRow['anchor'];
        }

        return $rows;
    }

    /**
     * @param GenIndexTermData|null $term
     *
     * @return GenIndexRowData|null
     */
    private function findFirstLinkRow(array|null $term): array|null
    {
        if ($term === null) {
            return null;
        }

        $row = $this->findFirstLinkRowInRows($term['rows']);
        if ($row !== null) {
            return $row;
        }

        foreach ($term['subterms'] as $subterm) {
            $row = $this->findFirstLinkRowInRows($subterm['rows']);
            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param array<int, GenIndexRowData> $rows
     *
     * @return GenIndexRowData|null
     */
    private function findFirstLinkRowInRows(array $rows): array|null
    {
        foreach ($rows as $row) {
            if ($row['kind'] === GenIndexRowKind::Link) {
                return $row;
            }
        }

        return null;
    }

    private function normalize(string $term): string
    {
        return mb_strtolower(trim($term));
    }
}
