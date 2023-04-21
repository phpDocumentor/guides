<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Integration;

use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Cli\Command\Run;
use phpDocumentor\Guides\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder as SymfonyFinder;

use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use function array_filter;
use function array_walk;
use function assert;
use function escapeshellarg;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function setlocale;
use function str_replace;
use function system;
use function trim;

use const LC_ALL;

class IntegrationBootstrapTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        $this->prepareContainer([
            GuidesExtension::class => [
                'template_paths' => [
                    __DIR__ . '/../../packages/guides-theme-bootstrap/resources/template',
                ],
            ],
        ]);
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /** @param string[] $compareFiles */
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
        if (file_exists($inputPath . '/skip')) {
            $this->markTestIncomplete($inputPath);
        }

        system('mkdir ' . escapeshellarg($outputPath));

        $command = $this->getContainer()->get(Run::class);
        assert($command instanceof Run);

        $input = new ArrayInput(
            [
                'input' => $inputPath,
                'output' => $outputPath,
                '--output-format' => ['html', 'intersphinx'],
            ],
            $command->getDefinition(),
        );

        $outputBuffer = new BufferedOutput();

        $command->run(
            $input,
            $outputBuffer,
        );

        foreach ($compareFiles as $compareFile) {
            $outputFile = str_replace($expectedPath, $outputPath, $compareFile);
            self::assertFileEqualsTrimmed($compareFile, $outputFile);
        }
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file. It ignores empty lines and whitespace at the start and end of each line
     *
     * @throws InvalidArgumentException
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
        return self::getTestsForDirectory('/tests-bootstrap');
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

            $tests[$dir->getPathname()] = [
                $dir->getPathname() . '/input',
                $dir->getPathname() . '/expected',
                $dir->getPathname() . '/temp',
                $compareFiles,
            ];
        }

        return $tests;
    }
}
