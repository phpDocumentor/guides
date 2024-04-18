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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

final class ModifyDocumentEntryAdditionalData
{
    /** @param array<string, Node> $additionalData */
    public function __construct(
        private array $additionalData,
        private readonly DocumentNode $documentNode,
        private readonly CompilerContextInterface $compilerContext,
    ) {
    }

    /** @return array<string, Node> */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /** @param array<string, Node> $additionalData */
    public function setAdditionalData(array $additionalData): ModifyDocumentEntryAdditionalData
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    public function getDocumentNode(): DocumentNode
    {
        return $this->documentNode;
    }

    public function getCompilerContext(): CompilerContextInterface
    {
        return $this->compilerContext;
    }
}
