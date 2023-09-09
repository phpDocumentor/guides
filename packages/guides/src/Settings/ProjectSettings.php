<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Settings;

use function is_array;
use function is_string;

class ProjectSettings
{
    /** @var array<string, string> */
    private array $inventories;
    private string $title;
    private string $version;
    private string $htmlTheme;

    /** @param array<string, string|array<string, string>> $settingsArray */
    public function __construct(array $settingsArray)
    {
        $this->title = isset($settingsArray['title']) && is_string($settingsArray['title']) ? $settingsArray['title'] : '';
        $this->version = isset($settingsArray['version']) && is_string($settingsArray['version']) ? $settingsArray['version'] : '';
        $this->inventories = isset($settingsArray['inventories']) && is_array($settingsArray['inventories']) ? $settingsArray['inventories'] : [];
        $this->htmlTheme = isset($settingsArray['html_theme']) && is_string($settingsArray['html_theme']) ? $settingsArray['html_theme'] : 'default';
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

    public function getHtmlTheme(): string
    {
        return $this->htmlTheme;
    }

    /** @param array<string, string> $inventories*/
    public function setInventories(array $inventories): void
    {
        $this->inventories = $inventories;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function setHtmlTheme(string $htmlTheme): void
    {
        $this->htmlTheme = $htmlTheme;
    }
}
