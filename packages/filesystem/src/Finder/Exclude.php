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

namespace phpDocumentor\FileSystem\Finder;

use phpDocumentor\FileSystem\Path;

use function array_map;
use function array_values;
use function str_starts_with;

final class Exclude
{
    /** @var list<string> */
    private readonly array $paths;

    /** @param list<string|Path> $paths */
    public function __construct(
        array $paths = [],
        private readonly bool $hidden = false,
        private readonly bool $symlinks = false,
    ) {
        $this->paths = array_values(
            array_map(
                static function (string|Path $path): string {
                    if (str_starts_with((string) $path, '/')) {
                        return (string) $path;
                    }

                    return '/' . $path;
                },
                $paths,
            ),
        );
    }

    /** @return list<string> */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function excludeHidden(): bool
    {
        return $this->hidden;
    }

    public function followSymlinks(): bool
    {
        return $this->symlinks;
    }

    /** @param list<string|path> $excludePaths */
    public function withPaths(array $excludePaths): self
    {
        return new self(
            $excludePaths,
            $this->hidden,
            $this->symlinks,
        );
    }
}
