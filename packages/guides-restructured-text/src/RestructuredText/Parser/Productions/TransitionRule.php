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
use phpDocumentor\Guides\Nodes\SeparatorNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#transitions
 *
 * @implements Rule<SeparatorNode>
 */
final class TransitionRule implements Rule
{
    public const SEPERATOR_LENGTH_MIN = 4;

    public function applies(DocumentParserContext $documentParser): bool
    {
        $line = $documentParser->getDocumentIterator()->current();
        $nextLine = $documentParser->getDocumentIterator()->getNextLine();

        return $this->currentLineIsASeparator($line, $nextLine) !== null;
    }

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();

        $overlineLetter = $this->currentLineIsASeparator(
            $documentIterator->current(),
            $documentIterator->getNextLine()
        );

        if ($overlineLetter !== null) {
            $documentIterator->next();
        }

        return new SeparatorNode(1);
    }

    private function currentLineIsASeparator(string $line, ?string $nextLine): ?string
    {
        $letter = LineChecker::isSpecialLine($line, self::SEPERATOR_LENGTH_MIN);
        if (!LinesIterator::isNullOrEmptyLine($nextLine)) {
            return null;
        }

        return $letter;
    }
}
