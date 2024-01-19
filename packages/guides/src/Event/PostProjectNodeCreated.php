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
