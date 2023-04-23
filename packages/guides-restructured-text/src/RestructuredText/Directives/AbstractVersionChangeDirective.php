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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;

/** @see https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-versionadded */
abstract class AbstractVersionChangeDirective extends SubDirective
{
    public function __construct(private readonly string $type, private readonly string $label)
    {
    }

    /** {@inheritDoc} */
    final public function processSub(
        DocumentNode $document,
        string $variable,
        string $data,
        array $options,
    ): Node|null {
        return (new VersionChangeNode(
            $this->type,
            $this->label,
            $data,
            $document->getChildren(),
        ))->withOptions($this->optionsToArray($options));
    }

    final public function getName(): string
    {
        return $this->type;
    }
}
