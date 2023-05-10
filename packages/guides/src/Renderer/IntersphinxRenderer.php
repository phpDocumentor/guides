<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Meta\ProjectMeta;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function json_encode;

use const JSON_PRETTY_PRINT;

class IntersphinxRenderer implements TypeRenderer
{
    final public const TYPE = 'intersphinx';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ProjectMeta $projectMeta,
    ) {
    }

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }

    public function render(RenderCommand $renderCommand): void
    {
        $inventory = [
            'std:doc' => [],
        ];

        foreach ($renderCommand->getMetas()->getAll() as $key => $documentEntry) {
            $url = $this->urlGenerator->canonicalUrl(
                '',
                $this->urlGenerator->createFileUrl($documentEntry->getFile(), 'html'),
            );
            $inventory['std:doc'][$key] = [
                $this->projectMeta->getTitle(),
                $this->projectMeta->getVersion(),
                $url,
                $documentEntry->getTitle()->toString(),
            ];
        }

        $json = (string) json_encode($inventory, JSON_PRETTY_PRINT);
        $renderCommand->getDestination()->put(
            'objects.inv.json',
            $json,
        );
    }
}
