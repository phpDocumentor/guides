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

namespace phpDocumentor\Guides\Meta;

class AnnotationTarget extends InternalTarget
{
    public function __construct(string $documentPath, string $anchorName, private readonly string $name)
    {
        parent::__construct($documentPath, $anchorName);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
