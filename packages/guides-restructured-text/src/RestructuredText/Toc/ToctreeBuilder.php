<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\Nodes\Menu\MenuDefinitionLineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\References\EmbeddedReferenceParser;

use function array_filter;
use function array_map;

class ToctreeBuilder
{
    use EmbeddedReferenceParser;

    /**
     * @param mixed[] $options
     *
     * @return MenuDefinitionLineNode[]
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

    /** @return MenuDefinitionLineNode[] */
    private function parseToctreeEntryLines(LinesIterator $lines): array
    {
        $linesArray =  array_filter(
            array_map('trim', $lines->toArray()),
            static fn (string $file): bool => $file !== '',
        );

        $result = [];
        foreach ($linesArray as $line) {
            $referenceData = $this->extractEmbeddedReference($line);
            $result[] = new MenuDefinitionLineNode($referenceData->reference, $referenceData->text);
        }

        return $result;
    }
}
