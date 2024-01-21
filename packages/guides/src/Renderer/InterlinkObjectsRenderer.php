<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function json_encode;

use const JSON_PRETTY_PRINT;

final class InterlinkObjectsRenderer implements TypeRenderer
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
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
            $renderCommand->getDocumentArray(),
            $renderCommand->getOrigin(),
            $renderCommand->getDestination(),
            $renderCommand->getDestinationPath(),
            'html',
        )->withOutputFilePath('objects.inv.json');

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
                    $this->urlGenerator->createFileUrl($context, $internalTarget->getDocumentPath(), $internalTarget->getAnchor()),
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
