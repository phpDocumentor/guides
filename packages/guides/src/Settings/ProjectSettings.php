<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Settings;

use function is_array;
use function is_string;

class ProjectSettings
{
    /** @var array<string, string> */
    private readonly array $inventories;
    private readonly string $title;
    private readonly string $version;

    /** @param array<string, string|array<string, string>> $settingsArray */
    public function __construct(array $settingsArray)
    {
        $this->title = isset($settingsArray['title']) && is_string($settingsArray['title']) ? $settingsArray['title'] : '';
        $this->version = isset($settingsArray['version']) && is_string($settingsArray['version']) ? $settingsArray['version'] : '';
        $this->inventories = isset($settingsArray['inventories']) && is_array($settingsArray['inventories']) ? $settingsArray['inventories'] : [];
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /** @return array<string, string> */
    public function getInventories(): array
    {
        return $this->inventories;
    }
}
