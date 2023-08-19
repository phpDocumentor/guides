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
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\GridTableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableSeparatorLineConfig;
use Psr\Log\LoggerInterface;

use function mb_strlen;
use function preg_match;
use function sprintf;
use function strlen;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#grid-tables
 *
 * @implements Rule<TableNode>
 */
final class GridTableRule implements Rule
{
    public const PRIORITY = 50;

    public function __construct(private readonly LoggerInterface $logger, private readonly RuleContainer $productions, private readonly GridTableBuilder $builder)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return $this->isColumnDefinitionLine($blockContext->getDocumentIterator()->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();
        $line = $documentIterator->current();

        $tableSeparatorLineConfig = $this->tableLineConfig($line, '-');
        $context = new ParserContext();
        $context->pushSeparatorLine($tableSeparatorLineConfig);
        $context->pushSeparatorLine($tableSeparatorLineConfig);

        $lineLength = mb_strlen($line);
        $headerRows = 0;
        $lineNumber = 1;

        while ($documentIterator->getNextLine() !== null) {
            $lineNumber++;
            $documentIterator->next();

            if ($lineLength !== mb_strlen($documentIterator->current())) {
                $message = sprintf(
                    "Malformed table: Line\n\n%s\n\ndoes not appear to be a complete table row",
                    $documentIterator->current(),
                );

                $this->logger->error($message, $blockContext->getLoggerInformation());
            }

            if ($this->isHeaderDefinitionLine($documentIterator->current())) {
                $separatorLineConfig = $this->tableLineConfig($documentIterator->current(), '=');
                $context->pushSeparatorLine($separatorLineConfig);
                if ($context->getHeaderRows() !== 0) {
                    $context->addError(
                        sprintf(
                            'Malformed table: multiple "header rows" using "===" were found. See table '
                            . 'lines "%d" and "%d"',
                            $context->getHeaderRows() + 1,
                            $lineNumber,
                        ),
                    );
                }

                $context->setHeaderRows($lineNumber - 1);
                continue;
            }

            if ($this->isColumnDefinitionLine($documentIterator->current())) {
                $separatorLineConfig = $this->tableLineConfig($documentIterator->current(), '-');
                $context->pushSeparatorLine($separatorLineConfig);
                // if an empty line follows a separator line, then it is the end of the table
                if (LinesIterator::isEmptyLine($documentIterator->peek())) {
                    break;
                }

                continue;
            }

            $context->pushContentLine($documentIterator->current());
        }

        return $this->builder->buildNode($context, $blockContext, $this->productions);
    }

    private function tableLineConfig(string $line, string $char): TableSeparatorLineConfig
    {
        $parts = [];
        $strlen = strlen($line);

        $currentPartStart = 1;
        for ($i = 1; $i < $strlen; $i++) {
            if ($line[$i] !== '+') {
                continue;
            }

            $parts[] = [$currentPartStart, $i];
            $currentPartStart = ++$i;
        }

        return new TableSeparatorLineConfig(
            $char === '=',
            $parts,
            $char,
            $line,
        );
    }

    private function isColumnDefinitionLine(string $line): bool
    {
        return $this->isDefintionLine($line, '-');
    }

    private function isHeaderDefinitionLine(string $line): bool
    {
        return $this->isDefintionLine($line, '=');
    }

    private function isDefintionLine(string $line, string $char): bool
    {
        return preg_match('/^(?:\+' . $char . '+)+\+$/', trim($line)) > 0;
    }
}
