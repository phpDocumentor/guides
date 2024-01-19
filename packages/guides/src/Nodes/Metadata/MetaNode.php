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

namespace phpDocumentor\Guides\Nodes\Metadata;

final class MetaNode extends MetadataNode
{
    public function __construct(protected string $key, string $value)
    {
        parent::__construct($value);
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
