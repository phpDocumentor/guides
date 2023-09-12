<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function json_encode;

use const JSON_PRETTY_PRINT;

class IntersphinxRenderer implements TypeRenderer
{
    final public const TYPE = 'intersphinx';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
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

        $context = RenderContext::forProject(
            $projectNode,
            $renderCommand->getOrigin(),
            $renderCommand->getDestination(),
            $renderCommand->getDestinationPath(),
            'html',
        );

        foreach ($renderCommand->getProjectNode()->getAllDocumentEntries() as $key => $documentEntry) {
            $url = $this->documentNameResolver->canonicalUrl(
                '',
                $this->urlGenerator->createFileUrl($context, $documentEntry->getFile()),
            );
            $inventory['std:doc'][$key] = [
                $projectNode->getTitle(),
                $projectNode->getVersion(),
                $url,
                $documentEntry->getTitle()->toString(),
            ];
        }

        foreach ($renderCommand->getProjectNode()->getAllInternalTargets() as $linkType => $targets) {
            if (isset($inventory[$linkType])) {
                $inventory[$linkType] = [];
            }

            foreach ($targets as $key => $internalTarget) {
                $url = $this->documentNameResolver->canonicalUrl(
                    '',
                    $this->urlGenerator->createFileUrl($context, $internalTarget->getDocumentPath(), 'html', $internalTarget->getAnchor()),
                );
                $inventory[$linkType][$key] = [
                    $projectNode->getTitle(),
                    $projectNode->getVersion(),
                    $url,
                    $internalTarget->getTitle(),
                ];
            }
        }

        $json = (string) json_encode($inventory, JSON_PRETTY_PRINT);
        $renderCommand->getDestination()->put(
            'objects.inv.json',
            $json,
        );
    }
}
