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

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\AbstractInlineParser;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function sprintf;

/** @extends AbstractBlockParser<TitleNode> */
final class HeaderParser extends AbstractBlockParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        private readonly iterable $inlineParsers,
        private readonly LoggerInterface $logger,
        private readonly AsciiSlugger $idGenerator,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): Node
    {
        $content = [];

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                foreach ($this->inlineParsers as $subParser) {
                    if ($subParser->supports($event)) {
                        $content[] = $subParser->parse($parser, $walker, $commonMarkNode);
                        break;
                    }
                }

                continue;
            }

            // leaving the heading node
            if ($commonMarkNode instanceof Heading) {
                return new TitleNode(
                    new InlineCompoundNode($content),
                    $commonMarkNode->getLevel(),
                    $this->idGenerator->slug($content[0]->toString() ?? '')->lower()->toString(),
                );
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'Header'));
        }

        throw new RuntimeException('Unexpected end of NodeWalker');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof Heading;
    }
}
