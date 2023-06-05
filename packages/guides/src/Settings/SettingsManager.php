<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Settings;

class SettingsManager
{
    private ProjectSettings $projectSettings;

    public function __construct(ProjectSettings|null $projectSettings = null)
    {
        $this->projectSettings = $projectSettings ?? new ProjectSettings();
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
