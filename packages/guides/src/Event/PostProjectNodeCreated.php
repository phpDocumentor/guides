<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;

final class PostProjectNodeCreated
{
    public function __construct(
        private ProjectNode $projectNode,
        private ProjectSettings $settings,
    ) {
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function setProjectNode(ProjectNode $projectNode): void
    {
        $this->projectNode = $projectNode;
    }

    public function getSettings(): ProjectSettings
    {
        return $this->settings;
    }

    public function setSettings(ProjectSettings $settings): void
    {
        $this->settings = $settings;
    }
}
