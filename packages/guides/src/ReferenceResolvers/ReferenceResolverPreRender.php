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

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\NodeRenderers\PreRenderers\PreNodeRenderer;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

use function array_merge;
use function sprintf;

final class ReferenceResolverPreRender implements PreNodeRenderer
{
    public function __construct(
        private readonly DelegatingReferenceResolver $referenceResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof LinkInlineNode;
    }

    public function execute(Node $node, RenderContext $renderContext): Node
    {
        Assert::isInstanceOf($node, LinkInlineNode::class);
        $messages = new Messages();
        $resolved = $this->referenceResolver->resolve($node, $renderContext, $messages);
        if (!$resolved) {
            $this->logger->warning(
                $messages->getLastWarning()?->getMessage() ?? sprintf(
                    'Reference %s could not be resolved in %s',
                    $node->getTargetReference(),
                    $renderContext->getCurrentFileName(),
                ),
                array_merge($renderContext->getLoggerInformation(), $messages->getLastWarning()?->getDebugInfo() ?? []),
            );
        }

        return $node;
    }
}
