<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function array_filter;
use function array_map;

class ToctreeBuilder
{
    /**
     * @param mixed[] $options
     *
     * @return string[]
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

    /** @return string[] */
    private function parseToctreeFiles(LinesIterator $lines): array
    {
        return array_filter(
            array_map('trim', $lines->toArray()),
            static fn (string $file): bool => $file !== '',
        );
    }
}
