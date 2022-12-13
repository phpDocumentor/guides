<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Metas;

final class MetasPass implements CompilerPass
{
    private Metas $metas;

    public function __construct(Metas $metas)
    {
        $this->metas = $metas;
    }

    public function run(array $documents): array
    {
        foreach ($documents as $document) {
            $this->metas->set(
                $document->getFilePath(),
                $document->getTitle(),
                $document->getTitles(),
                $document->getTocs(),
                0,
                $document->getDependencies()
            );
        }

        return $documents;
    }

    public function getPriority(): int
    {
        return 10000;
    }
}
