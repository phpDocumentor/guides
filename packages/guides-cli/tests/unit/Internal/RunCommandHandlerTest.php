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

namespace phpDocumentor\Guides\Cli\Internal;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Twig\Theme\ThemeConfig;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Twig\Loader\FilesystemLoader;

use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

final class RunCommandHandlerTest extends TestCase
{
    /** @param list<string> $existingFiles */
    #[DataProvider('provideInputFiles')]
    public function testTheInputFileIsParsedWithTheFormatItExistsIn(string $inputFormat, array $existingFiles, string $expected): void
    {
        $input = sys_get_temp_dir() . '/guides-run-' . uniqid();
        mkdir($input);
        foreach ($existingFiles as $file) {
            touch($input . '/' . $file);
        }

        $settings = new ProjectSettings();
        $settings->setInput($input);
        $settings->setInputFile('spec');
        $settings->setInputFormat($inputFormat);
        $settings->setOutput($input . '/output');
        $settings->setOutputFormats([]);

        $extensions = [];
        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->method('handle')->willReturnCallback(
            static function (object $command) use (&$extensions): mixed {
                if ($command instanceof ParseFileCommand) {
                    $extensions[] = $command->getExtension();

                    return new DocumentNode('hash', $command->getFile());
                }

                self::assertInstanceOf(CompileDocumentsCommand::class, $command);

                return $command->getDocuments();
            },
        );

        $themeManager = new ThemeManager(new FilesystemLoader(), []);
        $themeManager->registerTheme(new ThemeConfig('default', []));

        try {
            (new RunCommandHandler($commandBus, $themeManager, new NullLogger()))
                ->handle(new RunCommand($settings, new ProjectNode(), null));
        } finally {
            foreach ($existingFiles as $file) {
                unlink($input . '/' . $file);
            }

            if (is_dir($input . '/output')) {
                rmdir($input . '/output');
            }

            rmdir($input);
        }

        self::assertSame([$expected], $extensions);
    }

    /** @return iterable<string, array{string, list<string>, string}> */
    public static function provideInputFiles(): iterable
    {
        yield 'one format' => ['rst', ['spec.rst'], 'rst'];
        yield 'second of two formats' => ['rst,md', ['spec.md'], 'md'];
        yield 'first of two formats' => ['rst,md', ['spec.rst'], 'rst'];
        yield 'no file, first format' => ['rst,md', [], 'rst'];
    }
}
