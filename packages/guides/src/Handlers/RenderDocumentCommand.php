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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;

final class RenderDocumentCommand
{
    public function __construct(
        private readonly DocumentNode $document,
        private readonly RenderContext $renderContext,
    ) {
    }

    public function getDocument(): DocumentNode
    {
        return $this->document;
    }

    public function getContext(): RenderContext
    {
        return $this->renderContext;
    }

    public function getFileDestination(): string
    {
        return $this->renderContext->getDestinationPath() . '/' . $this->renderContext->getCurrentFileName() . '.' . $this->renderContext->getOutputFormat();
    }
}
