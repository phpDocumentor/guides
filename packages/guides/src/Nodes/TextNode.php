<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/** @extends AbstractNode<string> */
abstract class TextNode extends AbstractNode
{
    public function __construct(string $value)
    {
        $this->setValue($value);
    }

    /**
     * @param string[] $lines
     */
    protected static function normalizeLines(array $lines): string
    {
        if ($lines !== []) {
            $firstLine = $lines[0];

            $length = strlen($firstLine);
            for ($k = 0; $k < $length; $k++) {
                if (trim($firstLine[$k]) !== '') {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        return implode("\n", $lines);
    }
}
