<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node as CommonMarkNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use Psr\Log\LoggerInterface;

use function assert;
use function is_string;

/** @extends AbstractInlineTextDecoratorParser<HyperLinkNode> */
final class LinkParser extends AbstractInlineTextDecoratorParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        iterable $inlineParsers,
        LoggerInterface $logger,
    ) {
        parent::__construct($inlineParsers, $logger);
    }

    protected function getType(): string
    {
        return 'Link';
    }

    protected function createInlineNode(CommonMarkNode $commonMarkNode, string|null $content): InlineNode
    {
        assert($commonMarkNode instanceof Link);
        if (is_string($content)) {
            return new HyperLinkNode($content, $commonMarkNode->getUrl());
        }

        return new HyperLinkNode($commonMarkNode->getUrl(), $commonMarkNode->getUrl());
    }

    protected function supportsCommonMarkNode(CommonMarkNode $commonMarkNode): bool
    {
        return $commonMarkNode instanceof Link;
    }
}
