<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\Nodes\Menu\ParsedMenuEntryNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function array_map;

class ToctreeBuilder
{
    /**
     * @param mixed[] $options
     *
     * @return ParsedMenuEntryNode[]
     */
    public function buildToctreeFiles(
        ParserContext $parserContext,
        LinesIterator $lines,
        array $options,
    ): array {
        $toctreeFiles = [];

        foreach ($this->parseToctreeFiles($lines) as $file) {
            $toctreeFiles[] = $file;
        }

        return $toctreeFiles;
    }

    /** @return ParsedMenuEntryNode[] */
    private function parseToctreeFiles(LinesIterator $lines): array
    {
        $linesArray = $lines->toArray();
        $trimmedLines = array_map('trim', $linesArray);

        $result = [];
        foreach ($trimmedLines as $file) {
            if ($file === '') {
                continue;
            }

            $result[] = new ParsedMenuEntryNode($file);
        }

        return $result;
    }
}
