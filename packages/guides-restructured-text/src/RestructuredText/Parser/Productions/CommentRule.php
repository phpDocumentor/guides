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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function preg_match;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#comments
 */
final class CommentRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isCommentLine($documentParser->getDocumentIterator()->current());
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $buffer = new Buffer();
        $buffer->push($documentIterator->current());

        while ($documentIterator->getNextLine() !== null && $this->isCommentLine($documentIterator->getNextLine())) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        // TODO: Would we want to keep a comment as a Node in the AST?
        return null;
    }

    private function isCommentLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        return $this->isComment($line) || (trim($line) !== '' && $line[0] === ' ');
    }

    private function isComment(string $line): bool
    {
        return preg_match('/^\.\. (.*)$/mUsi', $line) > 0;
    }
}
