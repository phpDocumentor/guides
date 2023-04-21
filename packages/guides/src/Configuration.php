<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Nodes\Node;

final class Configuration
{
    /** @var string[] */
    private array $templatePaths;

    /** @param string[]|null $templatePaths */
    public function __construct(?array $templatePaths = null)
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

    /** @return array<class-string<Node>, string>  */
    public function htmlNodeTemplates(): array
    {
        return require __DIR__ . '/../resources/config/html.php';
    }
}
