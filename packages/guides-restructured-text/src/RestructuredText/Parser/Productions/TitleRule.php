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
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineTokenParser;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function mb_strlen;
use function min;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#sections
 *
 * @implements Rule<TitleNode>
 */
class TitleRule implements Rule
{
    private const TITLE_LENGTH_MIN = 2;

    public function __construct(private readonly InlineTokenParser $inlineTokenParser)
    {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        $line = $documentParser->getDocumentIterator()->current();
        $nextLine = $documentParser->getDocumentIterator()->getNextLine();

        return $this->currentLineIsAnOverline($line, $nextLine)
            || $this->nextLineIsAnUnderline($line, $nextLine);
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $title = '';
        $overlineLetter = $this->currentLineIsAnOverline(
            $documentIterator->current(),
            $documentIterator->getNextLine(),
        );

        if ($overlineLetter !== '') {
            $documentIterator->next();
            $title = trim($documentIterator->current()); // Title with over and underlines may be indented
        }

        $underlineLetter = $this->nextLineIsAnUnderline($documentIterator->current(), $documentIterator->getNextLine());
        if ($underlineLetter !== '') {
            if (($overlineLetter === '' || $overlineLetter === $underlineLetter)) {
                $title = trim($documentIterator->current()); // Title with over and underlines may be indented
            } else {
                $underlineLetter = '';
            }
        }

        $documentIterator->next();
        $documentIterator->next();

        $context = $documentParserContext->getContext();

        $letter = $overlineLetter ?: $underlineLetter;
        $level = $documentParserContext->getLevel($overlineLetter, $underlineLetter);

        return new TitleNode(
            $this->inlineTokenParser->parse($title, $context),
            $level,
            (new AsciiSlugger())->slug($title)->lower()->toString(),
        );
    }

    private function currentLineIsAnOverline(string $line, string|null $nextLine): string
    {
        $letter = LineChecker::isSpecialLine($line, self::TITLE_LENGTH_MIN);
        if (LinesIterator::isNullOrEmptyLine($nextLine)) {
            return '';
        }

        if (mb_strlen($line) < min(mb_strlen($nextLine), 4)) {
            return '';
        }

        return $letter ?? '';
    }

    private function nextLineIsAnUnderline(string $line, string|null $nextLine): string
    {
        $letter = LineChecker::isSpecialLine($nextLine ?? '', self::TITLE_LENGTH_MIN);

        if (LinesIterator::isEmptyLine($line)) {
            return '';
        }

        return $letter ?? '';
    }
}
