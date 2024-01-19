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

namespace phpDocumentor\Guides\Handlers;

use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Nodes\DocumentNode;

final class CompileDocumentsHandler
{
    public function __construct(private readonly Compiler $compiler)
    {
    }

    /** @return DocumentNode[] */
    public function handle(CompileDocumentsCommand $command): array
    {
        return $this->compiler->run($command->getDocuments(), $command->getCompilerContext());
    }
}
