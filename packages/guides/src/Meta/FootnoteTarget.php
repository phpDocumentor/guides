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

final class FootnoteTarget extends AnnotationTarget
{
    public function __construct(string $documentPath, string $anchorName, string $name, private int $number)
    {
        parent::__construct($documentPath, $anchorName, $name);
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
        $this->anchorName = 'footnote-' . $number;
    }
}
