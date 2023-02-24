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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\AdmonitionNode;

abstract class AbstractAdmonitionDirective extends SubDirective
{
    private string $name;

    private string $text;

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    final public function processSub(
        Node   $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return (new AdmonitionNode(
            $this->name,
            $this->text,
            $document
        ))->withOptions($this->optionsToArray($options));
    }

    final public function getName(): string
    {
        return $this->name;
    }
}
