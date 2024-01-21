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

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\Nodes\Menu\ExternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\References\EmbeddedReferenceParser;

use function array_filter;
use function array_map;
use function filter_var;
use function str_contains;

use const FILTER_VALIDATE_URL;

final class ToctreeBuilder
{
    use EmbeddedReferenceParser;

    /**
     * @param mixed[] $options
     *
     * @return MenuEntryNode[]
     */
    public function buildToctreeEntries(
        ParserContext $parserContext,
        LinesIterator $lines,
        array $options,
    ): array {
        $toctreeEntries = [];

        foreach ($this->parseToctreeEntryLines($lines) as $entry) {
            $toctreeEntries[] = $entry;
        }

        return $toctreeEntries;
    }

    /** @return MenuEntryNode[] */
    private function parseToctreeEntryLines(LinesIterator $lines): array
    {
        $linesArray =  array_filter(
            array_map('trim', $lines->toArray()),
            static fn (string $file): bool => $file !== '',
        );

        $result = [];
        foreach ($linesArray as $line) {
            $referenceData = $this->extractEmbeddedReference($line);
            if (filter_var($referenceData->reference, FILTER_VALIDATE_URL) !== false) {
                $result[] = new ExternalMenuEntryNode($referenceData->reference, TitleNode::fromString($referenceData->text ?? $referenceData->reference));
                continue;
            }

            if (str_contains($referenceData->reference, '*')) {
                $result[] = new GlobMenuEntryNode($referenceData->reference);
                continue;
            }

            $result[] = new InternalMenuEntryNode($referenceData->reference, $referenceData->text === null ? null : TitleNode::fromString($referenceData->text));
        }

        return $result;
    }
}
