<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Table;

use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\TableSeparatorLineConfig;

abstract class AbstractTableBuilder implements TableBuilder
{
    /** @var array<int, TableSeparatorLineConfig> */
    protected array $separatorLineConfigs = [];

    /** @var string[] */
    protected array $rawDataLines = [];

    /** @var int */
    protected $currentLineNumber = 0;

    private array $errors = [];

    public function pushSeparatorLine(TableSeparatorLineConfig $lineConfig): void
    {
        $this->separatorLineConfigs[$this->currentLineNumber] = $lineConfig;
        $this->currentLineNumber++;
    }

    public function pushContentLine(string $line): void
    {
        $this->rawDataLines[$this->currentLineNumber] = $line;
        $this->currentLineNumber++;
    }

    public function buildNode(MarkupLanguageParser $parser, LineChecker $lineChecker)
    {
        $tableNode = $this->compile();

        if (count($this->errors) > 0) {
            $tableAsString = $this->getTableAsString();
            $parser->getEnvironment()
                ->addError(sprintf("%s\nin file %s\n\n%s", $this->errors[0], $parser->getEnvironment()->getCurrentFileName(), $tableAsString));

            $this->data = [];
            $this->headers = [];

            return null;
        }

        foreach ($tableNode->getData() as $row) {
            foreach ($row->getColumns() as $col) {
                $lines = explode("\n", $col->getContent());

                if ($lineChecker->isListLine($lines[0], false)) {
                    $node = $parser->parseFragment($col->getContent())->getNodes()[0];
                } else {
                    //TODO: fix this, as we need to parse table contents for links
                    $node = new SpanNode($col->getContent()); //SpanNode::create($parser, $col->getContent());
                }

                $col->setNode($node);
            }
        }

        return $tableNode;
    }

    private function getTableAsString(): string
    {
        $lines = [];
        $i = 0;
        while (isset($this->separatorLineConfigs[$i]) || isset($this->rawDataLines[$i])) {
            if (isset($this->separatorLineConfigs[$i])) {
                $lines[] = $this->separatorLineConfigs[$i]->getRawContent();
            } else {
                $lines[] = $this->rawDataLines[$i];
            }

            $i++;
        }

        return implode("\n", $lines);
    }

    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    abstract protected function compile(): TableNode;
}
