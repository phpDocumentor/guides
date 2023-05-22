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

class InternalTarget
{
    public function __construct(
        private readonly string $documentPath,
        private readonly string $anchorName,
        private readonly string|null $title = null,
    ) {
    }

    public function getDocumentPath(): string
    {
        return $this->documentPath;
    }

    public function getAnchor(): string
    {
        return $this->anchorName;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }
}
