<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function array_filter;
use function array_map;
use function in_array;
use function str_contains;

class ToctreeBuilder
{
    public function __construct(private readonly GlobSearcher $globSearcher, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

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
            if ($this->isGlob($options, $file)) {
                $globPattern = $file;

                $globFiles = $this->globSearcher
                    ->globSearch($parserContext, $globPattern);

                foreach ($globFiles as $globFile) {
                    // if glob finds a file already explicitly defined
                    // don't duplicate it in the toctree again
                    if (in_array($globFile, $toctreeFiles, true)) {
                        continue;
                    }

                    $toctreeFiles[] = $globFile;
                }
            } else {
                $absoluteUrl = $this->urlGenerator->absoluteUrl(
                    $parserContext->getDirName(),
                    $file,
                );

                $toctreeFiles[] = $absoluteUrl;
            }
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

    /** @param mixed[] $options */
    private function isGlob(array $options, string $file): bool
    {
        return isset($options['glob']) && str_contains($file, '*');
    }
}
