<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Settings;

class ProjectSettings
{
    public function __construct(
        private string|null $title = null,
        private string|null $version = null,
    ) {
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }
}
