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

use phpDocumentor\FileSystem\Finder\Exclude;
use Psr\Log\LogLevel;

final class ProjectSettings
{
    /** @var array<string, string> */
    private array $inventories = [];
    private string $title = '';
    private string $version = '';
    private string $release = '';
    private string $copyright = '';
    private string $theme = 'default';
    private string $input = 'docs';
    private string $inputFile = '';
    private string $indexName = 'index,Index';
    private string $output = 'output';
    private string $inputFormat = 'rst';
    /** @var string[]  */
    private array $outputFormats = ['html'];
    private string $logPath = 'php://stder';

    /** @var LogLevel::*|null */
    private string|null $failOnError = null;
    private bool $showProgressBar = true;
    private bool $linksRelative = false;
    private string $defaultCodeLanguage = '';
    private int $maxMenuDepth = 0;
    private bool $automaticMenu = false;

    /** @var string[] */
    private array $ignoredDomains = [];
    private Exclude $excludes;

    public function __construct()
    {
        $this->excludes = new Exclude();
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

    public function getTheme(): string
    {
        return $this->theme;
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

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function setInput(string $input): void
    {
        $this->input = $input;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    public function getInputFormat(): string
    {
        return $this->inputFormat;
    }

    public function setInputFormat(string $inputFormat): void
    {
        $this->inputFormat = $inputFormat;
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    public function setLogPath(string $logPath): void
    {
        $this->logPath = $logPath;
    }

    public function isFailOnError(): bool
    {
        return $this->failOnError !== null;
    }

    /** @return LogLevel::* */
    public function getFailOnError(): string|null
    {
        return $this->failOnError;
    }

    /** @param LogLevel::* $logLevel */
    public function setFailOnError(string $logLevel): void
    {
        $this->failOnError = $logLevel;
    }

    public function isShowProgressBar(): bool
    {
        return $this->showProgressBar;
    }

    public function setShowProgressBar(bool $showProgressBar): void
    {
        $this->showProgressBar = $showProgressBar;
    }

    /** @return string[] */
    public function getOutputFormats(): array
    {
        return $this->outputFormats;
    }

    /** @param string[] $outputFormats */
    public function setOutputFormats(array $outputFormats): void
    {
        $this->outputFormats = $outputFormats;
    }

    public function isLinksRelative(): bool
    {
        return $this->linksRelative;
    }

    public function setLinksRelative(bool $linksRelative): void
    {
        $this->linksRelative = $linksRelative;
    }

    public function setDefaultCodeLanguage(string $defaultCodeLanguage): void
    {
        $this->defaultCodeLanguage = $defaultCodeLanguage;
    }

    public function getDefaultCodeLanguage(): string
    {
        return $this->defaultCodeLanguage;
    }

    public function getInputFile(): string
    {
        return $this->inputFile;
    }

    public function setInputFile(string $inputFile): void
    {
        $this->inputFile = $inputFile;
    }

    public function getRelease(): string
    {
        return $this->release;
    }

    public function setRelease(string $release): void
    {
        $this->release = $release;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function setCopyright(string $copyright): void
    {
        $this->copyright = $copyright;
    }

    public function getMaxMenuDepth(): int
    {
        return $this->maxMenuDepth;
    }

    public function setMaxMenuDepth(int $maxMenuDepth): ProjectSettings
    {
        $this->maxMenuDepth = $maxMenuDepth;

        return $this;
    }

    /** @return string[] */
    public function getIgnoredDomains(): array
    {
        return $this->ignoredDomains;
    }

    /** @param string[] $ignoredDomains */
    public function setIgnoredDomains(array $ignoredDomains): void
    {
        $this->ignoredDomains = $ignoredDomains;
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function setIndexName(string $indexName): ProjectSettings
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function isAutomaticMenu(): bool
    {
        return $this->automaticMenu;
    }

    public function setAutomaticMenu(bool $automaticMenu): ProjectSettings
    {
        $this->automaticMenu = $automaticMenu;

        return $this;
    }

    public function setExcludes(Exclude $exclude): void
    {
        $this->excludes = $exclude;
    }

    public function getExcludes(): Exclude
    {
        return $this->excludes;
    }
}
