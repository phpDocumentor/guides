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
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\Exception\UnknownTableType;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\GridTableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\SimpleTableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableParser;

use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#tables
 * @implements Rule<TableNode>
 */
final class TableRule implements Rule
{
    public const TYPE_PRETTY = 'pretty';
    public const TYPE_SIMPLE = 'simple';

    private LineChecker $lineChecker;

    private TableParser $tableParser;

    /** @var TableBuilder[] */
    private array $builders = [];

    public function __construct()
    {
        $this->lineChecker = new LineChecker();
        $this->tableParser = new TableParser();

        $this->builders[self::TYPE_SIMPLE] = new SimpleTableBuilder();
        $this->builders[self::TYPE_PRETTY] = new GridTableBuilder();
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->tableParser->parseTableSeparatorLine($documentParser->getDocumentIterator()->current()) !== null;
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $line = $documentIterator->current();
        if (trim($line) === '') {
            return null;
        }

        $type = $this->tableParser->guessTableType($line);
        if (isset($this->builders[$type]) === false) {
            throw new UnknownTableType($type);
        }
        $builder = $this->builders[$this->tableParser->guessTableType($line)];
        $tableSeparatorLineConfig = $this->tableParser->parseTableSeparatorLine($line);
        if ($tableSeparatorLineConfig === null) {
            return null;
        }

        $context = new ParserContext();

        $context->pushSeparatorLine($tableSeparatorLineConfig);
        $context->pushSeparatorLine($tableSeparatorLineConfig);

        while ($documentIterator->getNextLine() !== null) {
            $documentIterator->next();
            $separatorLineConfig = $this->tableParser->parseTableSeparatorLine($documentIterator->current());
            if ($separatorLineConfig !== null) {
                $context->pushSeparatorLine($separatorLineConfig);
                // if an empty line follows a separator line, then it is the end of the table
                if ($documentIterator->getNextLine() === null || trim($documentIterator->getNextLine()) === '') {
                    break;
                }

                continue;
            }

            $context->pushContentLine($documentIterator->current());
        }

        return $builder->buildNode($context, $documentParserContext->getParser(), $this->lineChecker);
    }
}
