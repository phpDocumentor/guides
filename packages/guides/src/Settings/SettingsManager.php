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

namespace phpDocumentor\Guides\Settings;

class SettingsManager
{
    public function __construct(
        private ProjectSettings $projectSettings = new ProjectSettings(),
    ) {
    }

    public function getProjectSettings(): ProjectSettings
    {
        return $this->projectSettings;
    }

    public function setProjectSettings(ProjectSettings $projectSettings): void
    {
        $this->projectSettings = $projectSettings;
    }
}
