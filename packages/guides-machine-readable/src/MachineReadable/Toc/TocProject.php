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

namespace phpDocumentor\Guides\MachineReadable\Toc;

use JsonSerializable;

/**
 * What "toc.json" says about the manual itself, once: its title and version,
 * and whatever a theme adds with {@see TocProject::setExtra()}.
 */
final class TocProject implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $extra = [];

    public function __construct(
        private string $title,
        private string $version,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setExtra(string $key, mixed $value): self
    {
        $this->extra[$key] = $value;

        return $this;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'version' => $this->version,
            ...$this->extra,
        ];
    }
}
