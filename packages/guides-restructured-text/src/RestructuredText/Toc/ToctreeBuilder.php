<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\UrlGenerator;

use function array_filter;
use function array_map;
use function explode;
use function in_array;
use function strpos;

class ToctreeBuilder
{
    private GlobSearcher $globSearcher;

    private UrlGenerator $urlGenerator;

    public function __construct(GlobSearcher $globSearcher, UrlGenerator $urlGenerator)
    {
        $this->globSearcher = $globSearcher;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param mixed[] $options
     *
     * @return string[]
     */
    public function buildToctreeFiles(
        ParserContext $environment,
        LinesIterator $lines,
        array $options
    ): array {
        $toctreeFiles = [];

        foreach ($this->parseToctreeFiles($lines) as $file) {
            if ($this->isGlob($options, $file)) {
                $globPattern = $file;

                $globFiles = $this->globSearcher
                    ->globSearch($environment, $globPattern);

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
                    $environment->getDirName(),
                    $file
                );

                $toctreeFiles[] = $absoluteUrl;
            }
        }

        return $toctreeFiles;
    }

    /**
     * @return string[]
     */
    private function parseToctreeFiles(LinesIterator $lines): array
    {
        return array_filter(
            array_map('trim', $lines->toArray()),
            static fn(string $file) => $file !== ''
        );
    }

    /**
     * @param mixed[] $options
     */
    private function isGlob(array $options, string $file): bool
    {
        return isset($options['glob']) && strpos($file, '*') !== false;
    }
}
