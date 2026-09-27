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
     * @param string[] $pathPrefixes
     */
    public function filterTermMap(GenIndexTermMap $termMap, array $pathPrefixes): GenIndexTermMap
    {
        $filtered = new GenIndexTermMap();
        foreach ($termMap as $key => $term) {
            $rows = $this->filterRowsByPath($term->getRows(), $pathPrefixes);

            $subterms = [];
            foreach ($term->getSubterms() as $subKey => $subterm) {
                $subRows = $this->filterRowsByPath($subterm->getRows(), $pathPrefixes);
                if ($subRows === []) {
                    continue;
                }

                $subterms[$subKey] = new GenIndexTermData($subterm->getTerm(), $subRows);
            }

            if ($rows === [] && $subterms === []) {
                continue;
            }

            $filtered->set($key, new GenIndexTermData($term->getTerm(), $rows, $subterms));
        }

        return $filtered;
    }

    /**
     * @param GenIndexRowData[] $rows
     * @param string[] $pathPrefixes
     *
     * @return GenIndexRowData[]
     */
    private function filterRowsByPath(array $rows, array $pathPrefixes): array
    {
        return array_values(array_filter($rows, static function (GenIndexRowData $row) use ($pathPrefixes): bool {
            foreach ($pathPrefixes as $prefix) {
                if (str_starts_with($row->filePath, $prefix)) {
                    return true;
                }
            }

            return false;
        }));
    }
}
