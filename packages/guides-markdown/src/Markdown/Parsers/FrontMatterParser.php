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

use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\Markdown\NullNode;
use phpDocumentor\Guides\Markdown\ParserInterface;
use phpDocumentor\Guides\Markdown\Parsers\FrontMatter\Parser as FieldParser;
use phpDocumentor\Guides\MarkupLanguageParser as GuidesParser;
use phpDocumentor\Guides\Nodes\Node;

use function array_key_exists;
use function is_array;

/** @implements ParserInterface<NullNode> */
final class FrontMatterParser implements ParserInterface
{
    /** @var array<string, FieldParser> */
    private array $fieldParsers;

    /** @param iterable<string, FieldParser> $fieldParsers */
    public function __construct(iterable $fieldParsers)
    {
        foreach ($fieldParsers as $parser) {
            $this->fieldParsers[$parser->field()] = $parser;
        }
    }

    public function parse(GuidesParser $parser, NodeWalker $walker, CommonMarkNode $current): Node
    {
        $frontMatter = $current->data->get('front_matter', []);
        if (is_array($frontMatter) === false) {
            return new NullNode('');
        }

        foreach ($frontMatter as $field => $value) {
            if (!array_key_exists($field, $this->fieldParsers)) {
                continue;
            }

            $this->fieldParsers[$field]->process($parser->getDocument(), $value, $frontMatter);
        }

        return new NullNode('');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->getNode() instanceof Document;
    }
}
