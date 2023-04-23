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

use RuntimeException;

use function is_string;
use function preg_match;
use function sprintf;

class ResolvedReference
{
    /** @param string[] $attributes */
    public function __construct(
        private readonly string|null $file,
        private readonly string $text,
        private readonly string|null $url,
        private readonly array $attributes = [],
    ) {
        $this->validateAttributes($attributes);
    }

    public function getFile(): string|null
    {
        return $this->file;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }

    /** @return string[] */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @param string[] $attributes */
    private function validateAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute => $_value) {
            if (
                !is_string($attribute)
                || $attribute === 'href'
                || !(bool) preg_match('/^[a-zA-Z\_][\w\.\-_]+$/', $attribute)
            ) {
                throw new RuntimeException(sprintf('Attribute with name "%s" is not allowed', $attribute));
            }
        }
    }
}
