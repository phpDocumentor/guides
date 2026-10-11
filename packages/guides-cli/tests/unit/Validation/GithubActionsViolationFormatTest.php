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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function explode;
use function getcwd;
use function rtrim;

final class GithubActionsViolationFormatTest extends TestCase
{
    private ProjectSettings $settings;
    private GithubActionsViolationFormat $format;

    protected function setUp(): void
    {
        $this->settings = new ProjectSettings();
        $this->settings->setInput('Documentation');
        $this->format = new GithubActionsViolationFormat(new AnnotationDetails(new SettingsManager($this->settings)));
    }

    public function testWarningIsAnnotatedOnItsFileAndLine(): void
    {
        $this->format->report(Violation::warning(
            'rst.code-block.empty',
            'The code-block has no content. Did you properly indent the code? ',
            ['rst-file' => 'Folder/Page', 'currentLineNumber' => 12],
        ));

        self::assertSame(
            ['::warning file=Documentation/Folder/Page.rst,line=12,title=rst.code-block.empty::The code-block has no content. Did you properly indent the code?'],
            $this->finish(),
        );
    }

    public function testErrorIsAnnotatedAsError(): void
    {
        $this->format->report(Violation::error('rst.table.malformed', 'Malformed table', ['rst-file' => 'index']));

        self::assertSame(
            ['::error file=Documentation/index.rst,title=rst.table.malformed::Malformed table'],
            $this->finish(),
        );
    }

    public function testViolationWithoutFileIsAnnotatedOnTheWholeRun(): void
    {
        $this->format->report(Violation::warning('rst.table.malformed', 'Malformed table'));

        self::assertSame(['::warning title=rst.table.malformed::Malformed table'], $this->finish());
    }

    #[DataProvider('fileProvider')]
    public function testFileIsThePathFromTheWorkingDirectory(string $input, string $inputFormat, string $document, string $expected): void
    {
        $this->settings->setInput($input);
        $this->settings->setInputFormat($inputFormat);

        $this->format->report(Violation::warning('rst.code-block.empty', 'Empty', ['rst-file' => $document]));

        self::assertSame(
            ['::warning file=' . $expected . ',title=rst.code-block.empty::Empty'],
            $this->finish(),
        );
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function fileProvider(): iterable
    {
        yield 'document without extension' => ['Documentation', 'rst', 'index', 'Documentation/index.rst'];
        yield 'document with extension' => ['Documentation', 'rst', 'index.rst', 'Documentation/index.rst'];
        yield 'markdown' => ['docs', 'md', 'Guide/index', 'docs/Guide/index.md'];
        yield 'input with trailing slash' => ['docs/', 'rst', 'index', 'docs/index.rst'];
        yield 'input relative to the working directory' => ['./docs', 'rst', 'index', 'docs/index.rst'];
        yield 'absolute input in the working directory' => [getcwd() . '/docs', 'rst', 'index', 'docs/index.rst'];
        yield 'absolute input elsewhere' => ['/srv/docs', 'rst', 'index', '/srv/docs/index.rst'];
    }

    public function testPlaceholdersAreReplacedFromTheContext(): void
    {
        $this->format->report(Violation::error(
            'rst.csv-table.file-not-readable',
            'Unable to read CSV file {file}',
            ['file' => 'data.csv', 'rst-file' => 'index'],
        ));

        self::assertSame(
            ['::error file=Documentation/index.rst,title=rst.csv-table.file-not-readable::Unable to read CSV file data.csv'],
            $this->finish(),
        );
    }

    public function testMessageIsEscapedToStayOnOneLine(): void
    {
        $this->format->report(Violation::warning('rst.table.malformed', "100% broken\nsecond line\r\nthird"));

        self::assertSame(
            ['::warning title=rst.table.malformed::100%25 broken%0Asecond line%0D%0Athird'],
            $this->finish(),
        );
    }

    public function testMentionsDoNotNotifyAnyone(): void
    {
        $this->format->report(Violation::warning('rst.table.malformed', 'Ask @someone or write to me@example.com'));

        self::assertSame(
            ['::warning title=rst.table.malformed::Ask `@someone` or write to me@example.com'],
            $this->finish(),
        );
    }

    public function testPropertiesAreEscaped(): void
    {
        $this->settings->setInput('docs');

        $this->format->report(Violation::warning('rst.odd:id,with', 'Odd', ['rst-file' => 'a,b:c']));

        self::assertSame(
            ['::warning file=docs/a%2Cb%3Ac.rst,title=rst.odd%3Aid%2Cwith::Odd'],
            $this->finish(),
        );
    }

    public function testAnnotationsArePrintedAlsoWhenQuiet(): void
    {
        $this->format->report(Violation::warning('rst.table.malformed', 'Malformed table'));

        self::assertSame(['::warning title=rst.table.malformed::Malformed table'], $this->finish(OutputInterface::VERBOSITY_QUIET));
    }

    public function testNothingIsPrintedWithoutViolations(): void
    {
        self::assertSame([], $this->finish());
    }

    /** @return list<string> The lines printed at the end of the run */
    private function finish(int $verbosity = OutputInterface::VERBOSITY_NORMAL): array
    {
        $output = new BufferedOutput($verbosity);
        $this->format->finish($output);
        $printed = rtrim($output->fetch(), "\n");

        return $printed === '' ? [] : explode("\n", $printed);
    }
}
