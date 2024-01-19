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

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;

/**
 * This event is dispatched right after the overall parsing process is
 * finished, Before the compiler passes, including the node transformers
 * are called.
 */
final class PostParseProcess
{
    /** @param DocumentNode[] $documents */
    public function __construct(
        private readonly ParseDirectoryCommand $parseDirectoryCommand,
        private readonly array $documents,
    ) {
    }

    public function getParseDirectoryCommand(): ParseDirectoryCommand
    {
        return $this->parseDirectoryCommand;
    }

    /** @return DocumentNode[] */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
