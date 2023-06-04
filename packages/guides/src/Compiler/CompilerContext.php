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

namespace phpDocumentor\Guides\Compiler;

use Exception;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;

class CompilerContext
{
    private DocumentNode|null $documentNode = null;

    public function __construct(
        private readonly ProjectNode $projectNode,
    ) {
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getDocumentNode(): DocumentNode
    {
        if ($this->documentNode === null) {
            throw new Exception('DocumentNode must be set in compiler context');
        }

        return $this->documentNode;
    }

    public function setDocumentNode(DocumentNode|null $documentNode): void
    {
        $this->documentNode = $documentNode;
    }
}
