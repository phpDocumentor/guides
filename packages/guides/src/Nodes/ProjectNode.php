<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/** @extends CompoundNode<DocumentNode> */
class ProjectNode extends CompoundNode
{
    public function __construct(
        private string|null $title = null,
        private string|null $version = null,
    ) {
        parent::__construct();
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
