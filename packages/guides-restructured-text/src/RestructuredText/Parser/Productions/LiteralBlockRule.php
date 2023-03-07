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
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function array_values;
use function count;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#indented-literal-blocks
 * @implements Rule<CodeNode>
 */
final class LiteralBlockRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        $iterator = $documentParser->getDocumentIterator();
        if ($iterator->current() !== '::') {
            return false;
        }

        return $this->isBlockLine($iterator->peek());
    }

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        //Skip the opening '::'
        $documentIterator->next();

        $buffer = new Buffer();
        $buffer->push($documentIterator->current());

        while ($documentIterator->getNextLine() !== null && $this->isBlockLine($documentIterator->getNextLine())) {
            $documentIterator->next();
            $buffer->push($documentIterator->current());
        }

        $lines = $this->removeLeadingWhitelines($buffer->getLines());
        if (count($lines) === 0) {
            return null;
        }

        //TODO this is a bug, we need LiteralBlockNode here
        return new LiteralBlockNode(implode("\n", $lines));
    }

    private function isBlockLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        if ($line !== '') {
            return trim($line[0]) === '';
        }

        return trim($line) === '';
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    private function removeLeadingWhitelines(array $lines): array
    {
        foreach ($lines as $index => $line) {
            if (trim($line) !== '') {
                break;
            }

            unset($lines[$index]);
        }

        return array_values($lines);
    }
}
