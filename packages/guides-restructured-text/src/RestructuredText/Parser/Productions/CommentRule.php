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
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;

use function str_starts_with;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#comments
 *
 * @implements Rule<Node>
 */
final class CommentRule implements Rule
{
    public const PRIORITY = 60;

    public function applies(BlockContext $blockContext): bool
    {
        return $this->isComment($blockContext->getDocumentIterator()->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $buffer = new Buffer();
        $buffer->push($documentIterator->current());

        while ($documentIterator->getNextLine() !== null && $this->isCommentLine($documentIterator->getNextLine())) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        // TODO: Would we want to keep a comment as a Node in the AST?
        return null;
    }

    private function isCommentLine(string|null $line): bool
    {
        if ($line === null) {
            return false;
        }

        return $this->isComment($line) || trim($line) === '' || $line[0] === ' ';
    }

    /**
     * Every explicit markup block which is not a valid markup construct is regarded as a comment.
     */
    private function isComment(string $line): bool
    {
        if (trim($line) === '..') {
            return true;
        }

        if (!str_starts_with($line, '.. ')) {
            return false;
        }

        if (LineChecker::isDirective($line)) {
            return false;
        }

        if (LineChecker::isLink($line)) {
            return false;
        }

        return !LineChecker::isAnnotation($line);
    }
}
