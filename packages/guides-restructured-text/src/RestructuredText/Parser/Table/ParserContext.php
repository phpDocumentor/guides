<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Table;

use phpDocumentor\Guides\RestructuredText\Parser\TableSeparatorLineConfig;

final class ParserContext
{
    /** @var string[] */
    private array $rawDataLines = [];
    /** @var array<int, TableSeparatorLineConfig> */
    private array $separatorLineConfigs = [];
    /** @var int */
    private int $currentLineNumber = 0;

    private array $errors = [];

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function pushContentLine(string $line): void
    {
        $this->rawDataLines[$this->currentLineNumber] = $line;
        $this->currentLineNumber++;
    }

    public function getDataLines(): array
    {
        return $this->rawDataLines;
    }

    public function getLineSeparators(): array
    {
        return $this->separatorLineConfigs;
    }

    public function pushSeparatorLine(TableSeparatorLineConfig $lineConfig): void
    {
        $this->separatorLineConfigs[$this->currentLineNumber] = $lineConfig;
        $this->currentLineNumber++;
    }

    public function getTableAsString(): string
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

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
