<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function count;
use function sprintf;
use function var_export;

/** @extends AbstractInlineParser<EmphasisInlineNode> */
final class EmphasisParser extends AbstractInlineParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        private readonly iterable $inlineParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): InlineNode
    {
        $content = [];

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

            if ($commonMarkNode instanceof Emphasis) {
                if (count($content) > 0 && $content[0] instanceof PlainTextInlineNode) {
                    return new EmphasisInlineNode($content[0]->getValue());
                }

                $this->logger->warning(sprintf('Emphasis CONTEXT: Content of emphasis could not be interpreted: %s', var_export($content, true)));

                return new EmphasisInlineNode('');
            }

            $this->logger->warning(sprintf('Emphasis CONTEXT: I am leaving a %s node', $commonMarkNode::class));
        }

        throw new RuntimeException('Unexpected end of NodeWalker');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof Emphasis;
    }
}
