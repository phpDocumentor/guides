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

use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

use function glob;
use function is_dir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class EnvironmentBuilderTest extends TestCase
{
    private ThemeManager $themeManager;
    private string $cacheDir;
    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = __DIR__ . '/Theme/fixtures';
        $loader = new FilesystemLoader([], $this->fixtureDir);
        $this->themeManager = new ThemeManager($loader, []);
        $this->cacheDir = sys_get_temp_dir() . '/phpDocumentor-guides-twig-test-' . uniqid();
    }

    protected function tearDown(): void
    {
        // Clean up cache directory
        if (!is_dir($this->cacheDir)) {
            return;
        }

        $files = glob($this->cacheDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        rmdir($this->cacheDir);
    }

    public function testEnvironmentBuilderWithoutCaching(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager);
        $environment = $builder->getTwigEnvironment();

        self::assertFalse($environment->getCache());
        self::assertTrue($environment->isDebug());
    }

    public function testEnvironmentBuilderWithCaching(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager, [], $this->cacheDir);
        $environment = $builder->getTwigEnvironment();

        self::assertSame($this->cacheDir, $environment->getCache());
        self::assertTrue($environment->isDebug());
    }

    public function testDebugExtensionIsAlwaysAdded(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager);
        $environment = $builder->getTwigEnvironment();

        self::assertTrue($environment->hasExtension(DebugExtension::class));
    }

    public function testCustomExtensionsAreAdded(): void
    {
        $extension = $this->createMock(ExtensionInterface::class);
        $extensionClass = $extension::class;

        $builder = new EnvironmentBuilder($this->themeManager, [$extension]);
        $environment = $builder->getTwigEnvironment();

        self::assertTrue($environment->hasExtension($extensionClass));
    }

    public function testSetContextAddsGlobal(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager);
        $context = $this->createMock(RenderContext::class);

        $builder->setContext($context);
        $environment = $builder->getTwigEnvironment();

        $globals = $environment->getGlobals();
        self::assertArrayHasKey('env', $globals);
        self::assertSame($context, $globals['env']);
    }

    public function testAutoReloadIsEnabled(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager, [], $this->cacheDir);
        $environment = $builder->getTwigEnvironment();

        self::assertTrue($environment->isAutoReload());
    }

    public function testSetEnvironmentFactory(): void
    {
        $builder = new EnvironmentBuilder($this->themeManager);
        $originalEnvironment = $builder->getTwigEnvironment();

        $loader = new FilesystemLoader();
        $builder->setEnvironmentFactory(static fn () => new Environment($loader));

        $newEnvironment = $builder->getTwigEnvironment();

        self::assertNotSame($originalEnvironment, $newEnvironment);
    }
}
