<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function preg_match;
use function str_starts_with;
use function trim;

/** @implements NodeTransformer<Node> */
class HyperLinkNodeTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if (!$node instanceof HyperLinkNode) {
            return $node;
        }

        if ($this->isAbsoluteUri($node->getUrl())) {
            return $node;
        }

        if (str_starts_with($node->getUrl(), 'mailto:')) {
            return $node;
        }

        if (str_starts_with($node->getUrl(), '#')) {
            $anchorName = trim($node->getUrl(), '#');

            return new ReferenceNode('', $node->getValue(), $anchorName);
        }

        $uri = $compilerContext->getDocumentNode()->getLink($node->getUrl());
        if ($uri === null) {
            $this->logger->warning('Reference ' . $node->getUrl() . ' could not be resolved in ' . $compilerContext->getDocumentNode()->getFilePath());
        }

        $node->setUrl($uri ?? '');

        return $node;
    }

    private function isAbsoluteUri(string $link): bool
    {
        // a modified version of @gruber's
        // "Liberal Regex Pattern for all URLs", https://gist.github.com/gruber/249502
        $absoluteUriPattern = '#(?i)\b((?:[a-z][\w\-+.]+:(?:/{1,3}|[a-z0-9%]))('
            . '?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>'
            . ']+|(\([^\s()<>]+\)))*\)|[^\s\`!()\[\]{};:\'".,<>?«»“”‘’]))#';

        return preg_match($absoluteUriPattern, $link) === 1;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof HyperLinkNode;
    }

    public function getPriority(): int
    {
        // Before Reference Node Transformer
        return 4000;
    }
}
