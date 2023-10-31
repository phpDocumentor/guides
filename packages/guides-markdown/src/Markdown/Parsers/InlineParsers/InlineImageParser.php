<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Node as CommonMarkNode;
use phpDocumentor\Guides\Nodes\Inline\ImageInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use Psr\Log\LoggerInterface;

use function assert;

/** @extends AbstractInlineTextDecoratorParser<ImageInlineNode> */
final class InlineImageParser extends AbstractInlineTextDecoratorParser
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
        return 'Image';
    }

    protected function createInlineNode(CommonMarkNode $commonMarkNode, string|null $content): InlineNode
    {
        assert($commonMarkNode instanceof Image);

        return new ImageInlineNode($commonMarkNode->getUrl(), $content ?? '');
    }

    protected function supportsCommonMarkNode(CommonMarkNode $commonMarkNode): bool
    {
        return $commonMarkNode instanceof Image;
    }
}
