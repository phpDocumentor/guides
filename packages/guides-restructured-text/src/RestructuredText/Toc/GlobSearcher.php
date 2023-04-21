<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use Flyfinder\Specification\Glob;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function rtrim;

class GlobSearcher
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /** @return string[] */
    public function globSearch(ParserContext $parserContext, string $globPattern): array
    {
        $fileSystem = $parserContext->getOrigin();
        $files = $fileSystem->find(
            new Glob(rtrim($parserContext->absoluteRelativePath(''), '/') . '/' . $globPattern),
        );
        $allFiles = [];
        foreach ($files as $file) {
            $allFiles[] = $this->urlGenerator->absoluteUrl($parserContext->getDirName(), $file['filename']);
        }

        return $allFiles;
    }
}
