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

namespace phpDocumentor\Guides\References;

class InternalTarget
{
    /** @var string */
    private $documentPath;

    /** @var string */
    private $anchorName;

    public function __construct(string $documentPath, string $anchorName)
    {
        $this->documentPath = $documentPath;
        $this->anchorName = $anchorName;
    }

    public function getDocumentPath(): string
    {
        return $this->documentPath;
    }

    public function getAnchor(): string
    {
        return $this->anchorName;
    }
}
