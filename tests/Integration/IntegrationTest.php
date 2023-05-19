<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Integration;

use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Cli\Command\Run;
use phpDocumentor\Guides\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder as SymfonyFinder;

use function array_filter;
use function array_walk;
use function assert;
use function escapeshellarg;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function setlocale;
use function str_ends_with;
use function str_replace;
use function system;
use function trim;

use const LC_ALL;

class IntegrationTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        self::prepareContainer(new Configuration());
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /** @param String[] $compareFiles */
    #[DataProvider('getTestsForDirectoryTest')]
    public function testHtmlIntegration(
        string $inputPath,
        string $expectedPath,
        string $outputPath,
        array $compareFiles,
    ): void {
        system('rm -rf ' . escapeshellarg($outputPath));
        self::assertDirectoryExists($inputPath);
        self::assertDirectoryExists($expectedPath);
        self::assertNotEmpty($compareFiles);

        $skip = file_exists($inputPath . '/skip');

        try {
            system('mkdir ' . escapeshellarg($outputPath));

            $command = $this->getContainer()->get(Run::class);
            assert($command instanceof Run);

            $input = new ArrayInput(
                [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    '--log-path' => $outputPath . '/logs',
                    '--output-format' => ['html', 'intersphinx'],
                ],
                $command->getDefinition(),
            );

            $outputBuffer = new BufferedOutput();

            $command->run(
                $input,
                $outputBuffer,
            );
            if (!file_exists($expectedPath . '/logs/error.log')) {
                self::assertFileDoesNotExist($outputPath . '/logs/error.log');
            }

            if (!file_exists($expectedPath . '/logs/warning.log')) {
                self::assertFileDoesNotExist($outputPath . '/logs/warning.log');
            }

            foreach ($compareFiles as $compareFile) {
                $outputFile = str_replace($expectedPath, $outputPath, $compareFile);
                if (str_ends_with($compareFile, '.log')) {
                    self::assertFileContainsLines($compareFile, $outputFile);
                } else {
                    self::assertFileEqualsTrimmed($compareFile, $outputFile, 'Expected file path: ' . $compareFile);
                }
            }
        } catch (ExpectationFailedException $e) {
            if ($skip) {
                $this->markTestIncomplete(file_get_contents($inputPath . '/skip') ?: '');
            }

            throw $e;
        }

        $this->assertFalse($skip, 'Test passes while marked as SKIP.');
    }

    /**
     * Asserts that each line of the expected file is contained in actual
     *
     * @throws ExpectationFailedException
     */
    public static function assertFileContainsLines(string $expected, string $actual): void
    {
        static::assertFileExists($expected);
        static::assertFileExists($actual);

        $lines = explode("\n", file_get_contents($expected));
        $actualContent =  file_get_contents($actual);
        foreach ($lines as $line) {
            static::assertStringContainsString($line, $actualContent, 'File "' . $actual . '" does not contain "' . $line . '"');
        }
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file. It ignores empty lines and whitespace at the start and end of each line
     *
     * @throws ExpectationFailedException
     */
    public static function assertFileEqualsTrimmed(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqual(self::getTrimmedFileContent($expected));

        static::assertThat(self::getTrimmedFileContent($actual), $constraint, $message);
    }

    public static function getTrimmedFileContent(string $file): string
    {
        $contentArray = explode("\n", file_get_contents($file));
        array_walk($contentArray, static function (&$value): void {
            $value = trim($value);
        });
        $contentArray = array_filter($contentArray, static function ($value) {
            return $value !== '';
        });

        return implode("\n", $contentArray);
    }

    /** @return mixed[] */
    public static function getTestsForDirectoryTest(): array
    {
        return self::getTestsForDirectory();
    }

    /** @return mixed[] */
    public static function getTestsForLatex(): array
    {
        return self::getTestsForDirectory('/tests-latex');
    }

    /** @return mixed[] */
    private static function getTestsForDirectory(string $directory = '/tests'): array
    {
        $finder = new SymfonyFinder();
        $finder
            ->directories()
            ->in(__DIR__ . $directory)
            ->depth('== 0');

        $tests = [];

        foreach ($finder as $dir) {
            if (!file_exists($dir->getPathname() . '/input')) {
                continue;
            }

            $compareFiles = [];
            $fileFinder = new SymfonyFinder();
            $fileFinder
                ->files()
                ->in($dir->getPathname() . '/expected');
            foreach ($fileFinder as $file) {
                $compareFiles[] = $file->getPathname();
            }

            $tests[$dir->getRelativePathname()] = [
                $dir->getPathname() . '/input',
                $dir->getPathname() . '/expected',
                $dir->getPathname() . '/temp',
                $compareFiles,
            ];
        }

        return $tests;
    }
}
