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

namespace phpDocumentor\Guides\Markdown\Parsers;

use phpDocumentor\Guides\Markdown\ParserInterface;
use phpDocumentor\Guides\Nodes\Node;

/**
 * @template TValue as Node
 * @implements ParserInterface<TValue>
 */
abstract class AbstractBlockParser implements ParserInterface
{
}
