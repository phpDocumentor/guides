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

namespace phpDocumentor\Guides\Functional;

use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\Attributes\DataProvider;

use function assert;
use function preg_replace;
use function str_replace;

class FunctionalAssetsTest extends ApplicationTestCase
{
    #[DataProvider('getFormats')]
    public function testAssets(string $format): void
    {
        $commandBus = $this->getContainer()->get(CommandBus::class);
        assert($commandBus instanceof CommandBus);

        $source = FlySystemAdapter::createForPath(__DIR__ . '/tests-assets/input-' . $format);
        $destination = FlySystemAdapter::createInMemory();
        $assetsDestination = FlySystemAdapter::createInMemory();
        $projectNode = new ProjectNode();

        $documents = $commandBus->handle(new ParseDirectoryCommand($source, '', $format, $projectNode));
        $documents = $commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));
        $commandBus->handle(new RenderCommand('html', $documents, $source, $destination, $projectNode, '/', $assetsDestination));

        static::assertTrue($assetsDestination->has('/images/logo.png'), '/images/logo.png exists on asset destination');

        static::assertTrue($destination->has('/index.html'), '/index.html exists on destination');
        static::assertStringContainsString('<img src="/images/logo.png"/>', self::normalizeString($destination->read('/index.html')));

        static::assertTrue($destination->has('/sub/article.html'), '/sub/article.html exists on destination');
        $subArticleContents = self::normalizeString($destination->read('/sub/article.html'));
        static::assertStringContainsString('<img src="/images/logo.png" alt="Relative path"/>', $subArticleContents);
        static::assertStringContainsString('<img src="/images/logo.png" alt="Absolute path"/>', $subArticleContents);
    }

    /** @return iterable<string, array<mixed>> */
    public static function getFormats(): iterable
    {
        yield 'rst' => ['rst'];
        yield 'md' => ['md'];
    }

    private static function normalizeString(string $str): string
    {
        return str_replace(' /', '/', preg_replace('/(?:\s|alt="")+/', ' ', $str));
    }
}
