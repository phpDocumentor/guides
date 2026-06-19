<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Search;

use phpDocumentor\Guides\Event\PostRenderProcess;

final class Indexer
{
    public function index(PostRenderProcess $postRenderProcess): void
    {
        $command = $postRenderProcess->getCommand();
        // Create a configuration
        $config = new \Pagefind\ServiceConfig(
            true,  // keep URL
            true,  // verbose mode
            'en'   // fallback language (optional, defaults to 'en')
        );

        $indexer = new \Pagefind\Indexer($config);
        $fileSystem = $command->getDestination();

        foreach ($fileSystem->listContents($command->getDestinationPath(), true) as $file)
        {
            if ($file['type'] !== 'file') {
                continue;
            }

            if (!str_ends_with($file['path'], '.html')) {
                continue;
            }

            $content = $fileSystem->read($file['path']);
            if ($content === false) {
                continue;
            }

            $indexer->addHtmlFile(
                $file['path'],
                $file['path'],
                $content
            );
        }

        foreach ($indexer->getFiles() as $file) {
            $fileSystem->put('pagefind/' . $file->getFileName(), $file->getContents());
        }
    }
}
