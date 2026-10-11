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

namespace phpDocumentor\Guides\Cli\Validation;

use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Validation\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

use function file_get_contents;
use function is_dir;
use function is_file;
use function json_decode;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const JSON_THROW_ON_ERROR;

final class GitlabViolationFormatTest extends TestCase
{
    private ProjectSettings $settings;
    private GitlabViolationFormat $format;
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/' . uniqid('guides-gitlab-', true);
        $this->settings = new ProjectSettings();
        $this->settings->setInput('Documentation');
        $this->settings->setViolationFile($this->directory . '/gl-code-quality-report.json');
        $settingsManager = new SettingsManager($this->settings);
        $this->format = new GitlabViolationFormat(new AnnotationDetails($settingsManager), $settingsManager);
    }

    protected function tearDown(): void
    {
        $file = $this->settings->getViolationFile();
        if ($file !== null && is_file($file)) {
            unlink($file);
        }

        if (!is_dir($this->directory)) {
            return;
        }

        rmdir($this->directory);
    }

    public function testWarningIsAMinorIssueOnItsFileAndLine(): void
    {
        $this->format->report(Violation::warning(
            'rst.code-block.empty',
            'The code-block has no content. Did you properly indent the code? ',
            ['rst-file' => 'Folder/Page', 'currentLineNumber' => 12],
        ));

        $issue = $this->finish()[0];

        self::assertSame('The code-block has no content. Did you properly indent the code?', $issue['description']);
        self::assertSame('rst.code-block.empty', $issue['check_name']);
        self::assertSame('minor', $issue['severity']);
        self::assertSame(['path' => 'Documentation/Folder/Page.rst', 'lines' => ['begin' => 12]], $issue['location']);
    }

    public function testErrorIsAMajorIssue(): void
    {
        $this->format->report(Violation::error('rst.table.malformed', 'Malformed table', ['rst-file' => 'index']));

        self::assertSame('major', $this->finish()[0]['severity']);
    }

    public function testViolationWithoutFileOrLineIsReportedOnTheInputDirectory(): void
    {
        $this->format->report(Violation::warning('rst.table.malformed', 'Malformed table'));

        self::assertSame(['path' => 'Documentation', 'lines' => ['begin' => 1]], $this->finish()[0]['location']);
    }

    public function testPlaceholdersAreReplacedFromTheContext(): void
    {
        $this->format->report(Violation::error(
            'rst.csv-table.file-not-readable',
            'Unable to read CSV file {file}',
            ['file' => 'data.csv', 'rst-file' => 'index'],
        ));

        self::assertSame('Unable to read CSV file data.csv', $this->finish()[0]['description']);
    }

    public function testFingerprintIsTheSameForTheSameIssueOnly(): void
    {
        $this->format->report(Violation::warning('rst.code-block.empty', 'Empty', ['rst-file' => 'index', 'currentLineNumber' => 3]));
        $this->format->report(Violation::warning('rst.code-block.empty', 'Empty', ['rst-file' => 'index', 'currentLineNumber' => 3]));
        $this->format->report(Violation::warning('rst.code-block.empty', 'Empty', ['rst-file' => 'index', 'currentLineNumber' => 4]));

        $issues = $this->finish();

        self::assertSame($issues[0]['fingerprint'], $issues[1]['fingerprint']);
        self::assertNotSame($issues[0]['fingerprint'], $issues[2]['fingerprint']);
    }

    public function testEmptyReportIsWrittenWithoutViolations(): void
    {
        self::assertSame([], $this->finish());
    }

    public function testWritingTheReportIsMentionedInTheOutput(): void
    {
        $output = new BufferedOutput();
        $this->format->finish($output);

        self::assertSame(
            'Code quality report for GitLab written to ' . $this->settings->getViolationFile() . "\n",
            $output->fetch(),
        );
    }

    /** @return list<array<string, mixed>> The issues in the written report */
    private function finish(): array
    {
        $this->format->finish(new BufferedOutput());

        /** @var list<array<string, mixed>> $issues */
        $issues = json_decode(
            (string) file_get_contents((string) $this->settings->getViolationFile()),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return $issues;
    }
}
