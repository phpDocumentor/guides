<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\UrlGeneratorInterface;

use function json_encode;

use const JSON_PRETTY_PRINT;

class IntersphinxRenderer implements TypeRenderer
{
    final public const TYPE = 'intersphinx';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
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
            'std:label' => [],
        ];
        $projectNode = $renderCommand->getProjectNode();

        foreach ($renderCommand->getProjectNode()->getAllDocumentEntries() as $key => $documentEntry) {
            $url = $this->urlGenerator->canonicalUrl(
                '',
                $this->urlGenerator->createFileUrl($documentEntry->getFile(), 'html'),
            );
            $inventory['std:doc'][$key] = [
                $projectNode->getTitle(),
                $projectNode->getVersion(),
                $url,
                $documentEntry->getTitle()->toString(),
            ];
        }

        foreach ($renderCommand->getProjectNode()->getAllInternalTargets() as $key => $internalTarget) {
            $url = $this->urlGenerator->canonicalUrl(
                '',
                $this->urlGenerator->createFileUrl($internalTarget->getDocumentPath(), 'html', $internalTarget->getAnchor()),
            );
            $inventory['std:label'][$key] = [
                $projectNode->getTitle(),
                $projectNode->getVersion(),
                $url,
                $internalTarget->getTitle(),
            ];
        }

        $json = (string) json_encode($inventory, JSON_PRETTY_PRINT);
        $renderCommand->getDestination()->put(
            'objects.inv.json',
            $json,
        );
    }
}
