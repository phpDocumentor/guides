<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\Nodes\Menu\MenuDefinitionLineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\EmbeddedUriParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function array_filter;
use function array_map;

class ToctreeBuilder
{
    use EmbeddedUriParser;

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
            $parsed = $this->extractEmbeddedUri($line);
            $result[] = new MenuDefinitionLineNode($parsed['uri'], $parsed['text']);
        }

        return $result;
    }
}
