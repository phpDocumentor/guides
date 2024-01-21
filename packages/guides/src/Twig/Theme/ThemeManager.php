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

namespace phpDocumentor\Guides\Twig\Theme;

use LogicException;
use Twig\Loader\FilesystemLoader;

use function array_keys;
use function array_merge;
use function implode;
use function sprintf;

final class ThemeManager
{
    /** @var array<string, ThemeConfig> */
    private array $themeMap = [];

    /** @param string[] $defaultPaths */
    public function __construct(
        private readonly FilesystemLoader $filesystemLoader,
        array $defaultPaths,
    ) {
        $filesystemLoader->setPaths($defaultPaths);

        $this->registerTheme(new ThemeConfig('default', $defaultPaths));
    }

    public function registerTheme(ThemeConfig $theme): void
    {
        $this->themeMap[$theme->name] = $theme;
    }

    public function useTheme(string $name): void
    {
        if (!isset($this->themeMap[$name])) {
            throw new LogicException(sprintf(
                'Theme "%s" is not registered, available themes are: %s',
                $name,
                implode(', ', array_keys($this->themeMap)),
            ));
        }

        $paths = [];
        $themeConfig = $this->themeMap[$name];
        do {
            $paths[] = $themeConfig->paths;

            if ($themeConfig->extends === null) {
                break;
            }

            if (!isset($this->themeMap[$themeConfig->extends])) {
                throw new LogicException(sprintf(
                    'Theme "%s" requires theme "%s", but it is not registered, available themes are: %s',
                    $name,
                    $themeConfig->extends,
                    implode(', ', array_keys($this->themeMap)),
                ));
            }

            $themeConfig = $this->themeMap[$themeConfig->extends];
        } while ($themeConfig);

        $paths[] = $this->themeMap['default']->paths;

        $this->filesystemLoader->setPaths(array_merge(...$paths));
    }

    public function getFilesystemLoader(): FilesystemLoader
    {
        return $this->filesystemLoader;
    }
}
