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
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

use function json_encode;
use function sprintf;
use function zlib_encode;

use const JSON_PRETTY_PRINT;
use const ZLIB_ENCODING_DEFLATE;

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
                $projectNode->getTitle() ?? '-',
                $projectNode->getVersion() ?? '-',
                $url,
                $documentEntry->getTitle()->toString() ?? '-',
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
                if ($internalTarget instanceof InternalTarget) {
                    $url = $this->documentNameResolver->canonicalUrl(
                        '',
                        $this->urlGenerator->createFileUrl($context, $internalTarget->getDocumentPath(), $internalTarget->getPrefix() . $internalTarget->getAnchor()),
                    );
                }

                $inventory[$linkType][$key] = [
                    $projectNode->getTitle() ?? '-',
                    $projectNode->getVersion() ?? '-',
                    $url,
                    $internalTarget->getTitle() ?? '-',
                ];
            }
        }

        $json = (string) json_encode($inventory, JSON_PRETTY_PRINT);
        $renderCommand->getDestination()->put(
            'objects.inv.json',
            $json,
        );

        $header = sprintf(
            <<<'EOF'
# Sphinx inventory version 2
# Project: %s
# Version: %s
# The remainder of this file is compressed using zlib.

EOF
            ,
            $renderCommand->getProjectNode()->getTitle() ?? '-',
            $renderCommand->getProjectNode()->getVersion() ?? '-',
        );
        $body = '';

        foreach ($inventory as $role => $entry) {
            foreach ($entry as $key => $value) {
                $body .= sprintf("%s %s %s %s %s\n", $key, $role, -1, $value[2], $value[3]);
            }
        }

        $encodedBody = zlib_encode($body, ZLIB_ENCODING_DEFLATE);
        $renderCommand->getDestination()->put(
            'objects.inv',
            $header . $encodedBody,
        );
    }
}
