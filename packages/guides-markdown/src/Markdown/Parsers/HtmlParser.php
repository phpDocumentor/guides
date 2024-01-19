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

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use Psr\Log\LoggerInterface;

use function assert;

/** @extends AbstractBlockParser<ParagraphNode> */
final class HtmlParser extends AbstractBlockParser
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): ParagraphNode
    {
        assert($current instanceof HtmlBlock);

        $this->logger->warning('We do not support plain HTML for security reasons. Escaping all HTML ');

        $walker->next();

        return new ParagraphNode([new InlineCompoundNode([new PlainTextInlineNode($current->getLiteral())])]);
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof HtmlBlock;
    }
}
