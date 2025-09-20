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

namespace phpDocumentor\Guides\RestructuredText;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;

final class BufferBench
{
    private Buffer $buffer;

    public function __construct()
    {
        $this->buffer = new Buffer();
        $this->buffer->push('   This is a line with leading spaces.   ');
        $this->buffer->push(' This is another line.');
        $this->buffer->push(' Yet another line with spaces.   ');
        $this->buffer->push(' Final line.');
    }

    #[Revs([1000, 10_000])]
    #[Iterations(5)]
    public function benchGetLines(): void
    {
        $this->buffer->getLines();
    }
}
