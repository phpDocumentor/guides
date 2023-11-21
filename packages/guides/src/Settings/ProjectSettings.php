<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Settings;

class ProjectSettings
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
    private string $output = 'output';
    private string $inputFormat = 'rst';
    /** @var string[]  */
    private array $outputFormats = ['html'];
    private string $logPath = 'php://stder';
    private bool $failOnError = false;
    private bool $showProgressBar = true;
    private bool $linksRelative = false;
    private string $defaultCodeLanguage = '';

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
        return $this->failOnError;
    }

    public function setFailOnError(bool $failOnError): void
    {
        $this->failOnError = $failOnError;
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
}
