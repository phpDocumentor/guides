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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\Node;

class AdmonitionNode extends Node
{
    private string $name;

    private string $text;

    public function __construct(string $name, string $text, Node $value)
    {
        parent::__construct($value);
        $this->name = $name;
        $this->text = $text;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
