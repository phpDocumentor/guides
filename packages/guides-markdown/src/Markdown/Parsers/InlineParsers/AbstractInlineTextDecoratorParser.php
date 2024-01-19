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

use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function count;
use function sprintf;
use function var_export;

/**
 * @template TValue as InlineNode
 * @extends AbstractInlineParser<TValue>
 */
abstract class AbstractInlineTextDecoratorParser extends AbstractInlineParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        private readonly iterable $inlineParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return TValue */
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): InlineNode
    {
        $content = [];

        if ($current->firstChild() === null) {
            // Handle inline nodes without content
            return $this->createInlineNode($current, null);
        }

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                foreach ($this->inlineParsers as $subParser) {
                    if (!$subParser->supports($event)) {
                        continue;
                    }

                    $content[] = $subParser->parse($parser, $walker, $commonMarkNode);
                }

                continue;
            }

            if ($this->supportsCommonMarkNode($commonMarkNode)) {
                if (count($content) === 1 && $content[0] instanceof PlainTextInlineNode) {
                    return $this->createInlineNode($commonMarkNode, $content[0]->getValue());
                }

                $this->logger->warning(sprintf('%s CONTEXT: Content of emphasis could not be interpreted: %s', $this->getType(), var_export($content, true)));

                return $this->createInlineNode($commonMarkNode, null);
            }

            $this->logger->warning(sprintf('%s context does not allow a %s node', $this->getType(), $commonMarkNode::class));
        }

        throw new RuntimeException(sprintf('Unexpected end of NodeWalker, %s context was not closed', $this->getType()));
    }

    abstract protected function getType(): string;

    /** @return TValue */
    abstract protected function createInlineNode(CommonMarkNode $commonMarkNode, string|null $content): InlineNode;

    abstract protected function supportsCommonMarkNode(CommonMarkNode $commonMarkNode): bool;

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $this->supportsCommonMarkNode($event->getNode());
    }
}
