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

use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Validation\Severity;
use phpDocumentor\Guides\Validation\Violation;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

use function dirname;
use function file_put_contents;
use function hash;
use function implode;
use function is_dir;
use function json_encode;
use function mkdir;
use function sprintf;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Writes the violations to a code quality report, which GitLab shows in a merge request and on the lines of its diff.
 *
 * GitLab reads the report from the file declared as "artifacts:reports:codequality" of the CI job.
 *
 * @link https://docs.gitlab.com/ci/testing/code_quality/#code-quality-report-format
 */
final class GitlabViolationFormat implements ViolationFormat
{
    public const DEFAULT_FILE = 'gl-code-quality-report.json';

    /** @var list<array{description: string, check_name: string, fingerprint: string, severity: string, location: array{path: string, lines: array{begin: int}}}> */
    private array $issues = [];

    public function __construct(
        private readonly AnnotationDetails $details,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function report(Violation $violation): void
    {
        // GitLab needs a file for each issue, the input directory is the closest for the others
        $path = $this->details->getFile($violation) ?? $this->details->getInputDirectory();
        $line = $this->details->getLine($violation) ?? 1;
        $message = $this->details->getMessage($violation);

        $this->issues[] = [
            'description' => $message,
            'check_name' => $violation->identifier,
            // GitLab compares the fingerprints of two reports to tell which issues a merge request adds or resolves
            'fingerprint' => hash('sha256', implode("\0", [$violation->identifier, $path, $line, $message])),
            'severity' => match ($violation->severity) {
                Severity::Warning => 'minor',
                Severity::Error => 'major',
            },
            'location' => [
                'path' => $path,
                'lines' => ['begin' => $line],
            ],
        ];
    }

    public function finish(OutputInterface $output): void
    {
        $file = $this->settingsManager->getProjectSettings()->getViolationFile() ?? self::DEFAULT_FILE;

        // The report is written also without violations, so GitLab sees that the merge request resolved them
        $directory = dirname($file);
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create the directory "%s" for the code quality report', $directory));
        }

        $report = json_encode(
            $this->issues,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR,
        );
        if (@file_put_contents($file, $report . "\n") === false) {
            throw new RuntimeException(sprintf('Could not write the code quality report to "%s"', $file));
        }

        $output->writeln(sprintf('Code quality report for GitLab written to %s', $file));
    }
}
