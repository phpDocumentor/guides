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

final class ExternalTarget implements Target
{
    public function __construct(
        private readonly string $url,
        private readonly string|null $title = null,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }
}
