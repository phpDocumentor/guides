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

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node as CommonMarkNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use Psr\Log\LoggerInterface;

use function assert;
use function filter_var;
use function str_ends_with;
use function substr;

use const FILTER_VALIDATE_URL;

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

    /** @param InlineNodeInterface[] $children */
    protected function createInlineNode(CommonMarkNode $commonMarkNode, string|null $content, array $children = []): InlineNodeInterface
    {
        assert($commonMarkNode instanceof Link);

        $url =  $commonMarkNode->getUrl();
        if (str_ends_with($url, '.md') && filter_var($url, FILTER_VALIDATE_URL) === false) {
            $url = substr($url, 0, -3);
        }

        return new HyperLinkNode($content ? [new PlainTextInlineNode($content)] : $children, $url);
    }

    protected function supportsCommonMarkNode(CommonMarkNode $commonMarkNode): bool
    {
        return $commonMarkNode instanceof Link;
    }
}
