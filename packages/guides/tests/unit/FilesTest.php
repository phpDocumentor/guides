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

namespace phpDocumentor\Guides;

use PHPUnit\Framework\TestCase;

use function iterator_to_array;

class FilesTest extends TestCase
{
    public function testFilesAreSorted(): void
    {
        $files = new Files();
        $files->add('page');
        $files->add('Subpage');
        $files->add('index');

        $result = iterator_to_array($files->getIterator());

        self::assertSame(
            [
                'index',
                'page',
                'Subpage',
            ],
            $result,
        );
    }
}
