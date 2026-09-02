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

use phpDocumentor\Guides\Nodes\Index\GenIndexRow;
use phpDocumentor\Guides\Nodes\Index\GenIndexRowKind;
use phpDocumentor\Guides\Nodes\Index\GenIndexTerm;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;

use function strcasecmp;
use function usort;

/**
 * Turns a (already see-resolved) term map into a sorted `GenIndexTerm` node
 * tree.
 */
final class GenIndexNodeBuilder
{
    /** @return GenIndexTerm[] */
    public function toTermNodes(GenIndexTermMap $termMap): array
    {
        $terms = [];
        foreach ($termMap as $data) {
            $terms[] = $this->toTermNode($data);
        }

        usort($terms, static fn (GenIndexTerm $a, GenIndexTerm $b): int => strcasecmp($a->getTerm(), $b->getTerm()));

        return $terms;
    }

    private function toTermNode(GenIndexTermData $data): GenIndexTerm
    {
        $subterms = [];
        foreach ($data->getSubterms() as $subtermData) {
            $subterms[] = $this->toTermNode(new GenIndexTermData($subtermData->getTerm(), $subtermData->getRows()));
        }

        usort($subterms, static fn (GenIndexTerm $a, GenIndexTerm $b): int => strcasecmp($a->getTerm(), $b->getTerm()));

        return new GenIndexTerm($data->getTerm(), $this->toRowNodes($data->getRows()), $subterms);
    }

    /**
     * @param GenIndexRowData[] $rows
     *
     * @return GenIndexRow[]
     */
    private function toRowNodes(array $rows): array
    {
        usort(
            $rows,
            static fn (GenIndexRowData $a, GenIndexRowData $b): int => ($a->kind === GenIndexRowKind::Link ? 1 : 0) <=> ($b->kind === GenIndexRowKind::Link ? 1 : 0),
        );

        $nodes = [];
        foreach ($rows as $row) {
            $reference = null;
            if ($row->anchor !== null) {
                $text = $row->kind === GenIndexRowKind::Link ? $row->title : $row->seeText;
                $reference = new ReferenceNode($row->anchor, [new PlainTextInlineNode($text ?? '')]);
                if ($row->main) {
                    $reference->setClasses(['main-entry']);
                }
            }

            $nodes[] = new GenIndexRow($row->kind, $row->main, $reference, $row->seeText);
        }

        return $nodes;
    }
}
