<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

use Monolog\Logger;

class ProjectMeta
{
    private string|null $title = null;
    private string|null $version = null;

    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        if ($this->version !== null) {
            $this->logger->warning('Project version was set more then one: ' . $this->title . ' and ' . $version);
        }

        $this->version = $version;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        if ($this->title !== null) {
            $this->logger->warning('Project title was set more then one: ' . $this->title . ' and ' . $title);
        }

        $this->title = $title;
    }
}
