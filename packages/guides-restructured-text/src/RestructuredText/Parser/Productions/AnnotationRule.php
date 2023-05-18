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

use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\AnnotationUtility;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function is_string;
use function preg_match;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#directives
 *
 * @implements Rule<Node>
 */
final class AnnotationRule implements Rule
{
    public const PRIORITY = 70;

    public function __construct(
        private readonly InlineMarkupRule $inlineMarkupRule,
    ) {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isAnnotation($documentParser->getDocumentIterator()->current());
    }

    private function isAnnotation(string $line): bool
    {
        return preg_match('/^\.\.\s+\[([#a-zA-Z0-9]*)\]\s(.*)$$/mUsi', $line) > 0;
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $openingLine = $documentIterator->current();
        preg_match('/^\.\.\s+\[([#a-zA-Z0-9]*)\]\s(.*)$$/mUsi', $openingLine, $matches);
        $annotationKey = $matches[1] ?? null;
        $content = $matches[2] ?? null;

        if (!is_string($annotationKey) || !is_string($content)) {
            return null;
        }

        $buffer = new Buffer();
        $buffer->push($content);

        while (
            $documentIterator->getNextLine() !== null
            && LinesIterator::isEmptyLine($documentIterator->getNextLine()) === false
        ) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        if (!AnnotationUtility::isFootnoteKey($annotationKey)) {
            $node = new CitationNode([], $annotationKey);
        } else {
            $node = new FootnoteNode(
                [],
                AnnotationUtility::getFootnoteName($annotationKey) ?? '',
                AnnotationUtility::getFootnoteNumber($annotationKey) ?? 0,
            );
        }

        $buffer->trimLines();
        $this->inlineMarkupRule->apply(
            $documentParserContext->withContents($buffer->getLinesString()),
            $node,
        );

        if ($documentIterator->getNextLine() !== null) {
            $documentIterator->next();
        }

        return $node;
    }
}
