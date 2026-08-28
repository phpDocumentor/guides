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

use function array_filter;
use function array_values;
use function str_starts_with;

/**
 * Restricts a (already see-resolved) term map to rows originating from a
 * document under one of a set of path prefixes -- used for a `.. genindex::`
 * directive's `:scope:` option.
 *
 * @phpstan-import-type GenIndexTermMap from IndexEntryCollector
 * @phpstan-import-type GenIndexRowData from IndexEntryCollector
 */
final class GenIndexTermMapFilter
{
    /**
     * Drops any term or subterm left with no rows and no surviving subterms
     * of its own. `see`/`seealso` rows are filtered by where the entry
     * itself was written, not by what it resolves to -- a target found
     * outside the visible scope still links correctly, it just isn't itself
     * listed as a separate row here.
     *
     * @param GenIndexTermMap $termMap
     * @param string[] $pathPrefixes
     *
     * @return GenIndexTermMap
     */
    public function filterTermMap(array $termMap, array $pathPrefixes): array
    {
        $filtered = [];
        foreach ($termMap as $key => $term) {
            $rows = $this->filterRowsByPath($term['rows'], $pathPrefixes);

            $subterms = [];
            foreach ($term['subterms'] as $subKey => $subterm) {
                $subRows = $this->filterRowsByPath($subterm['rows'], $pathPrefixes);
                if ($subRows === []) {
                    continue;
                }

                $subterms[$subKey] = ['term' => $subterm['term'], 'rows' => $subRows];
            }

            if ($rows === [] && $subterms === []) {
                continue;
            }

            $filtered[$key] = ['term' => $term['term'], 'rows' => $rows, 'subterms' => $subterms];
        }

        return $filtered;
    }

    /**
     * @param array<int, GenIndexRowData> $rows
     * @param string[] $pathPrefixes
     *
     * @return array<int, GenIndexRowData>
     */
    private function filterRowsByPath(array $rows, array $pathPrefixes): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($pathPrefixes): bool {
            foreach ($pathPrefixes as $prefix) {
                if (str_starts_with($row['filePath'], $prefix)) {
                    return true;
                }
            }

            return false;
        }));
    }
}
