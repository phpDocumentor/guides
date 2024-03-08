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

use phpDocumentor\Guides\Nodes\AnnotationListNode;
use phpDocumentor\Guides\Nodes\AnnotationNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\AnnotationUtility;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function preg_match;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#directives
 *
 * @implements Rule<AnnotationListNode>
 */
final class AnnotationRule implements Rule
{
    public const PRIORITY = 70;

    public function __construct(
        private readonly InlineMarkupRule $inlineMarkupRule,
    ) {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return LineChecker::isAnnotation($blockContext->getDocumentIterator()->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $openingLine = $documentIterator->current();
        [$annotationKey, $content] = $this->analyzeOpeningLine($openingLine);
        
        $name = '';
        $buffer = new Buffer();
        $buffer->push($content);
        $nodes = [];

        while (
            $documentIterator->getNextLine() !== null
            && LinesIterator::isEmptyLine($documentIterator->getNextLine()) === false
        ) {
            $documentIterator->next();
            if (LineChecker::isAnnotation($documentIterator->current())) {
                $nodes[] = $this->createAnnotationNode($annotationKey, $buffer, $blockContext, $documentIterator, $name);
                $openingLine = $documentIterator->current();
                [$annotationKey, $content] = $this->analyzeOpeningLine($openingLine);
                $buffer = new Buffer();
                $buffer->push($content);
            } else {
                $buffer->push($documentIterator->current());
            }
        }

        $nodes[] = $this->createAnnotationNode($annotationKey, $buffer, $blockContext, $documentIterator, $name);

        if ($documentIterator->getNextLine() !== null) {
            $documentIterator->next();
        }

        return new AnnotationListNode($nodes, $name);
    }
    
    private function createAnnotationNode(
        string $annotationKey,
        Buffer $buffer,
        BlockContext $blockContext,
        LinesIterator $documentIterator,
        string &$name,
    ): AnnotationNode {
        if (!AnnotationUtility::isFootnoteKey($annotationKey)) {
            $node = new CitationNode([], $annotationKey);
            $name = 'citation-list';
        } else {
            $node = new FootnoteNode(
                [],
                AnnotationUtility::getFootnoteName($annotationKey) ?? '',
                AnnotationUtility::getFootnoteNumber($annotationKey) ?? 0,
            );
            $name = 'footer-list';
        }

        $buffer->trimLines();
        $this->inlineMarkupRule->apply(
            new BlockContext(
                $blockContext->getDocumentParserContext(),
                $buffer->getLinesString(),
                false,
                $documentIterator->key(),
            ),
            $node,
        );

        return $node;
    }

    /** @return string[] */
    private function analyzeOpeningLine(string $openingLine): array
    {
        preg_match('/^\.\.\s+\[([#a-zA-Z0-9]*)\]\s(.*)$$/mUsi', $openingLine, $matches);
        $annotationKey = $matches[1] ?? '';
        $content = $matches[2] ?? '';

        return [$annotationKey, $content];
    }
}
