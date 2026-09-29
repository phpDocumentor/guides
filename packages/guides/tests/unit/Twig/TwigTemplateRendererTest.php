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

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

final class TwigTemplateRendererTest extends TestCase
{
    private const TEMPLATES = ['file.html.twig' => '{{ env.currentFileName }}|{{ debugInformation["rst-file"] }}'];

    private EnvironmentBuilder $environmentBuilder;

    private TwigTemplateRenderer $renderer;

    protected function setUp(): void
    {
        $this->environmentBuilder = new EnvironmentBuilder(new ThemeManager(new FilesystemLoader(), []));
        $this->environmentBuilder->setEnvironmentFactory(static fn () => new Environment(new ArrayLoader(self::TEMPLATES)));
        $this->renderer = new TwigTemplateRenderer($this->environmentBuilder);
    }

    public function testGlobalsFollowTheContext(): void
    {
        self::assertSame('first|first', $this->renderer->renderTemplate(self::context('first'), 'file.html.twig'));
        self::assertSame('second|second', $this->renderer->renderTemplate(self::context('second'), 'file.html.twig'));
    }

    public function testGlobalsAreSetOnlyWhenTheContextChanges(): void
    {
        $environment = new class (new ArrayLoader(self::TEMPLATES)) extends Environment {
            public int $addGlobalCalls = 0;

            public function addGlobal(string $name, mixed $value): void
            {
                $this->addGlobalCalls++;

                parent::addGlobal($name, $value);
            }
        };
        $this->environmentBuilder->setEnvironmentFactory(static fn () => $environment);
        $environment->addGlobalCalls = 0;

        $first = self::context('first');
        $this->renderer->renderTemplate($first, 'file.html.twig');
        $this->renderer->renderTemplate($first, 'file.html.twig');
        $this->renderer->renderTemplate($first, 'file.html.twig');

        self::assertSame(2, $environment->addGlobalCalls);
    }

    public function testContextSetFromOutsideIsReplaced(): void
    {
        $first = self::context('first');
        $this->renderer->renderTemplate($first, 'file.html.twig');

        $this->environmentBuilder->setContext(self::context('other'));

        self::assertSame('first|first', $this->renderer->renderTemplate($first, 'file.html.twig'));
    }

    public function testReplacedEnvironmentReceivesTheGlobals(): void
    {
        $first = self::context('first');
        $this->renderer->renderTemplate($first, 'file.html.twig');

        $this->environmentBuilder->setEnvironmentFactory(static fn () => new Environment(new ArrayLoader(self::TEMPLATES)));

        self::assertSame('first|first', $this->renderer->renderTemplate($first, 'file.html.twig'));
    }

    private static function context(string $file): RenderContext
    {
        $document = new DocumentNode('hash', $file);

        return RenderContext::forDocument(
            $document,
            [$document],
            FlySystemAdapter::createInMemory(),
            FlySystemAdapter::createInMemory(),
            '/path',
            'html',
            new ProjectNode(),
        );
    }
}
