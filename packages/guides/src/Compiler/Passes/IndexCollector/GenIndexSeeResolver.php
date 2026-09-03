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
 */
final class GenIndexSeeResolver
{
    public function resolveSeeRows(GenIndexTermMap $termMap): GenIndexTermMap
    {
        $resolved = new GenIndexTermMap();
        foreach ($termMap as $key => $term) {
            $subterms = [];
            foreach ($term->getSubterms() as $subKey => $subterm) {
                $subterms[$subKey] = new GenIndexTermData(
                    $subterm->getTerm(),
                    $this->resolveSeeRowTargets($subterm->getRows(), $termMap),
                );
            }

            $resolved->set($key, new GenIndexTermData(
                $term->getTerm(),
                $this->resolveSeeRowTargets($term->getRows(), $termMap),
                $subterms,
            ));
        }

        return $resolved;
    }

    /**
     * @param GenIndexRowData[] $rows
     *
     * @return GenIndexRowData[]
     */
    private function resolveSeeRowTargets(array $rows, GenIndexTermMap $termMap): array
    {
        foreach ($rows as $index => $row) {
            if ($row->kind === GenIndexRowKind::Link) {
                continue;
            }

            $targetKey = $this->normalize($row->seeText ?? '');
            $targetLinkRow = $this->findFirstLinkRow($termMap->get($targetKey));
            if ($targetLinkRow === null) {
                continue;
            }

            $rows[$index] = $row->withAnchor($targetLinkRow->anchor);
        }

        return $rows;
    }

    private function findFirstLinkRow(GenIndexTermData|null $term): GenIndexRowData|null
    {
        if ($term === null) {
            return null;
        }

        $row = $this->findFirstLinkRowInRows($term->getRows());
        if ($row !== null) {
            return $row;
        }

        foreach ($term->getSubterms() as $subterm) {
            $row = $this->findFirstLinkRowInRows($subterm->getRows());
            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /** @param GenIndexRowData[] $rows */
    private function findFirstLinkRowInRows(array $rows): GenIndexRowData|null
    {
        foreach ($rows as $row) {
            if ($row->kind === GenIndexRowKind::Link) {
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
