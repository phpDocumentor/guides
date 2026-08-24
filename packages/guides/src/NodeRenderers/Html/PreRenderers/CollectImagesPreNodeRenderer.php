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

namespace phpDocumentor\Guides\NodeRenderers\Html\PreRenderers;

use League\Uri\BaseUri;
use League\Uri\Uri;
use phpDocumentor\Guides\NodeRenderers\PreRenderers\PreNodeRenderer;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Inline\ImageInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;
use Throwable;

use function ltrim;
use function sprintf;

class CollectImagesPreNodeRenderer implements PreNodeRenderer
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof ImageNode || $node instanceof ImageInlineNode;
    }

    public function execute(Node $node, RenderContext $renderContext): Node
    {
        if (!$node instanceof ImageNode && !$node instanceof ImageInlineNode) {
            return $node;
        }

        $imageUrl = $node->getValue();
        if (BaseUri::from(Uri::new($imageUrl))->isAbsolute()) {
            return $node;
        }

        $absoluteImageUrl = $this->documentNameResolver->absoluteUrl($renderContext->getDirName(), $imageUrl);
        $node->setValue($absoluteImageUrl);

        try {
            if ($renderContext->getOrigin()->has($absoluteImageUrl) === false) {
                $this->logger->error(
                    sprintf('Image reference not found "%s"', $imageUrl),
                    $renderContext->getLoggerInformation(),
                );

                return $node;
            }

            $imageContents = $renderContext->getOrigin()->read($absoluteImageUrl);
            if ($imageContents === false) {
                $this->logger->error(
                    sprintf('Could not read image file "%s"', $imageUrl),
                    $renderContext->getLoggerInformation(),
                );

                return $node;
            }

            $result = $renderContext->getImageDestination()->put(
                '/' . ltrim($renderContext->getDestinationPath() . '/' . $absoluteImageUrl, '/'),
                $imageContents,
            );
            if ($result === false) {
                $this->logger->error(
                    sprintf('Unable to write image "%s"', $absoluteImageUrl),
                    $renderContext->getLoggerInformation(),
                );
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Unable to write file "%s", %s', $absoluteImageUrl, $e->getMessage()),
                $renderContext->getLoggerInformation(),
            );
        }

        return $node;
    }
}
