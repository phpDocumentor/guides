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

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use Psr\Log\LoggerInterface;

use function sprintf;

/** @extends AbstractInlineParser<PlainTextInlineNode> */
final class PlainTextParser extends AbstractInlineParser
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): PlainTextInlineNode
    {
        if (!$current instanceof Text) {
            $this->logger->warning(sprintf('Expected plaintext, encountered a %s node', $current::class));

            return new PlainTextInlineNode('');
        }

        return new PlainTextInlineNode($current->getLiteral());
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof Text;
    }
}
