<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers;

use phpDocumentor\Guides\Markdown\ParserInterface;
use phpDocumentor\Guides\Nodes\Node;

/**
 * @template TValue as Node
 * @implements ParserInterface<TValue>
 */
abstract class AbstractBlock implements ParserInterface
{
}
