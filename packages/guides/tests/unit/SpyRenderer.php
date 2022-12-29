<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

class SpyRenderer implements Renderer
{
    /**
     * @var mixed[]
     */
    private array $context;
    private string $template;

    /** @param mixed[] $context */
    public function render(string $template, array $context = []): string
    {
        $this->context = $context;
        $this->template = $template;

        return 'spy';
    }

    /** @return mixed[] */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function renderNode(Node $node, RenderContext $environment): string
    {
        return '';
    }

    public function renderDocument(DocumentNode $node, RenderContext $environment): string
    {
        return '';
    }
}
