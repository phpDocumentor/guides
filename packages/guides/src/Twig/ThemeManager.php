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

namespace phpDocumentor\Guides\Twig;

use Twig\Loader\FilesystemLoader;

use function array_merge;

class ThemeManager
{
    /** @var string[][] */
    private array $themeMap = [];

    /** @param string[] $defaultPaths */
    public function __construct(
        private FilesystemLoader $filesystemLoader,
        private array $defaultPaths,
    ) {
    }

    /** @param string[] $paths */
    public function registerTheme(string $name, array $paths): void
    {
        $this->themeMap[$name] = $paths;
    }

    public function useTheme(string $name): void
    {
        $this->filesystemLoader->setPaths(array_merge($this->themeMap[$name] ?? [], $this->defaultPaths));
    }

    public function getFilesystemLoader(): FilesystemLoader
    {
        return $this->filesystemLoader;
    }
}
