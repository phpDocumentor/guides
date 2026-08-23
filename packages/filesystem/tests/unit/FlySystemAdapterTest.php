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

namespace phpDocumentor\FileSystem;

use PHPUnit\Framework\TestCase;

use function array_map;
use function file_put_contents;
use function is_dir;
use function is_link;
use function mkdir;
use function rmdir;
use function scandir;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class FlySystemAdapterTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('guides-fs-', true);
        mkdir($this->root, 0777, true);

        file_put_contents($this->root . DIRECTORY_SEPARATOR . 'index.rst', 'Index');

        if (@symlink($this->root . DIRECTORY_SEPARATOR . 'index.rst', $this->root . DIRECTORY_SEPARATOR . 'link.rst') !== false) {
            return;
        }

        self::markTestSkipped('The filesystem does not support symbolic links');
    }

    protected function tearDown(): void
    {
        $this->remove($this->root);
    }

    public function testItListsADirectoryContainingSymbolicLinksInsteadOfAborting(): void
    {
        $contents = FlySystemAdapter::createForPath($this->root)->listContents('');

        $names = array_map(static fn (StorageAttributes $item): mixed => $item['basename'], $contents);

        self::assertContains('index.rst', $names, 'A regular file next to a symbolic link must still be listed');
        self::assertNotContains('link.rst', $names, 'A symbolic link is skipped rather than aborting the listing');
    }

    /** Removes a tree without following the symbolic links inside it. */
    private function remove(string $path): void
    {
        if (is_link($path) || !is_dir($path)) {
            unlink($path);

            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $this->remove($path . DIRECTORY_SEPARATOR . $entry);
        }

        rmdir($path);
    }
}
