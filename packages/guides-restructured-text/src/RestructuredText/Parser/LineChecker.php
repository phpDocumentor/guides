<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Lists\ListItem;
use function preg_match;

class LineChecker
{
    public function isListLine(string $line, bool $isCode): bool
    {
        $listLine = $this->parseListLine($line);

        if ($listLine !== null) {
            return $listLine->getDepth() === 0 || !$isCode;
        }

        return false;
    }

    private function parseListLine(string $line): ?ListItem
    {
        $depth = 0;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === ' ') {
                $depth++;
            } elseif ($char === "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match) > 0) {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                [$match[4]]
            );
        }

        if (strlen($line) === 1 && $line[0] === '-') {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                ['']
            );
        }

        return null;
    }
}
