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
use Psr\Log\LoggerInterface;

use function preg_match;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#comments
 *
 * @implements Rule<Node>
 */
final class CommentRule implements Rule
{
    public const PRIORITY = 60;

    /**
     * A directive name, e.g. "confval", "code-block" or "php:method", directly followed by "::" and more text. Without
     * whitespace after "::" the block is no directive, so it ends up as a comment.
     */
    private const DIRECTIVE_WITHOUT_SPACE_PATTERN = '/^\.\.\s+[a-zA-Z][\w:-]*?::\S/';

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return $this->isComment($blockContext->getDocumentIterator()->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $this->warnAboutDirectiveWithoutSpace($blockContext);
        $buffer = new Buffer();
        $buffer->push($documentIterator->current());

        while ($documentIterator->getNextLine() !== null && $this->isCommentLine($documentIterator->getNextLine())) {
            $documentIterator->next();
            // Consecutive comments are consumed as one block, check each of them
            if ($this->isComment($documentIterator->current())) {
                $this->warnAboutDirectiveWithoutSpace($blockContext);
            }

            $buffer->push($documentIterator->current());
        }

        // TODO: Would we want to keep a comment as a Node in the AST?
        return null;
    }

    /**
     * A typo like ".. confval::name" silently drops the whole block, as it is no directive, so it is ignored as a
     * comment. Real comments practically never start with a name directly followed by "::" and more text.
     */
    private function warnAboutDirectiveWithoutSpace(BlockContext $blockContext): void
    {
        $line = trim($blockContext->getDocumentIterator()->current());
        if (preg_match(self::DIRECTIVE_WITHOUT_SPACE_PATTERN, $line, $matches) !== 1) {
            return;
        }

        // The match ends with the first character after "::"
        $afterSeparator = strlen($matches[0]) - 1;
        $this->logger->warning(
            sprintf(
                'The comment "%s" looks like a directive without a space after "::"; it is ignored. Write "%s".',
                trim(substr($line, 2)),
                substr($line, 0, $afterSeparator) . ' ' . substr($line, $afterSeparator),
            ),
            $blockContext->getLoggerInformation(),
        );
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
