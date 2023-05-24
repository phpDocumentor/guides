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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function str_ends_with;
use function substr;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#paragraphs
 *
 * @implements Rule<ParagraphNode>
 */
final class ParagraphRule implements Rule
{
    public const PRIORITY = 10;

    public function __construct(
        private readonly InlineMarkupRule $inlineMarkupRule,
    ) {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        // Should be last in the series of rules; basically: if it ain't anything else, it is a paragraph.
        // This could prove to be wrong when we pull up the spec, but the existing implementation applies this concept
        // and we roll with it for now.
        return trim($documentParser->getDocumentIterator()->current()) !== '';
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $documentParserContext->getDocumentIterator();

        $buffer = new Buffer();
        $buffer->push($documentIterator->current());

        while (
            $documentIterator->getNextLine() !== null
            && LinesIterator::isEmptyLine($documentIterator->getNextLine()) === false
        ) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        $lastLine = trim($buffer->pop() ?? '');

        // https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#literal-blocks
        // 2 colons at the end means that the next Indented Block should be a LiteralBlock and we should remove the
        // colons
        if (str_ends_with($lastLine, '::')) {
            $lastLine = trim(substr($lastLine, 0, -2));

            // However, if a line ended in a double colon, we keep one colon
            if ($lastLine !== '' && !str_ends_with($lastLine, ':')) {
                $lastLine .= ':';
            }

            $documentParserContext->nextIndentedBlockShouldBeALiteralBlock = true;

            if ($lastLine !== '') {
                $buffer->push($lastLine);
            }
        } else {
            $buffer->push($lastLine);
        }

        if (trim($buffer->getLinesString()) === '') {
            return null;
        }

        $node = new ParagraphNode();

        $this->inlineMarkupRule->apply(
            $documentParserContext->withContents($buffer->getLinesString()),
            $node,
        );

        return $node;
    }
}
