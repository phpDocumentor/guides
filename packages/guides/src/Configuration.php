<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

final class Configuration
{
    /** @var string[] */
    private array $templatePaths;

    /** @param string[]|null $templatePaths */
    public function __construct(array|null $templatePaths = null)
    {
        $this->templatePaths = $templatePaths ?? [
            __DIR__ . '/../../../packages/guides/resources/template/html/guides',
        ];
    }

    /** @return string[] */
    public function getTemplatePaths(): array
    {
        return $this->templatePaths;
    }

    /** @param string[] $templatePaths */
    public function setTemplatePaths(array $templatePaths): void
    {
        $this->templatePaths = $templatePaths;
    }
}
